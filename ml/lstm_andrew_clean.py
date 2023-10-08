import tensorflow as tf
from tensorflow.keras.models import Sequential
from tensorflow.keras.layers import LSTM, Dense, Masking, Dropout
from tensorflow.keras.callbacks import EarlyStopping
from keras.models import load_model
from sklearn.preprocessing import StandardScaler
import numpy as np
import csv
import pandas as pd
import joblib
from sklearn.model_selection import train_test_split, cross_val_score
from tensorflow.keras.regularizers import l2
import matplotlib.pyplot as plt
from sklearn.metrics import accuracy_score


def remove_columns(arr: np.array, columns: int | list) -> np.array:
    return np.delete(arr, columns, axis=1)


def transform(history_data: np.array, dts_data: np.array) -> any:
    # join data by time (0 column)
    time_column = 0

    # NaN to Zero
    history_data = np.nan_to_num(history_data)
    dts_data = np.nan_to_num(dts_data)

    # Find the indices of the common values in the common column
    _, history_data_common, dts_data_common = \
        np.intersect1d(history_data[:, time_column], dts_data[:, time_column], return_indices=True)

    in_data = history_data[history_data_common]
    out_data = dts_data[dts_data_common]
    # remove time column from out
    in_data = remove_columns(in_data, time_column).astype('float32')
    out_data = remove_columns(out_data, time_column).astype('float32')

    return np.nan_to_num(in_data), np.nan_to_num(out_data)

X_data = pd.read_csv('discovr_summed_all.csv')
y_data = pd.read_csv('kyoto_minute_full.csv')

X_data, y_data = transform(X_data, y_data)
print("=== DATA PROCESSED ===")

seq_length = 24  # input data sequence hours
prediction_horizon = 24  # predict hours ahead

min_y = np.min(y_data)
if min_y < 0:
    y_data += -1 * min_y + 1
    storm_idx = np.where((y_data >= -100 - min_y) & (y_data <= -50 - min_y))[0]
scaler = StandardScaler()
X_data = scaler.fit_transform(X_data)
y_data = scaler.fit_transform(y_data)
max_norm_y = max(y_data)
min_norm_y = min(y_data)
max_norm_storm_y = max(y_data[storm_idx])
min_norm_storm_y = min(y_data[storm_idx])

size = len(X_data) - seq_length - prediction_horizon + 1
sequences = [0] * size
labels = [0] * size
for i in range(len(X_data) - seq_length - prediction_horizon + 1):
    # Extract historical data for the input sequence
    sequence_data = X_data[i:i + seq_length]

    # Extract the DST index value for 24 hours ahead
    # label_data = y_data[i + seq_length + prediction_horizon - 1]
    label_data = y_data[i:i + prediction_horizon - 1]
    # Append the sequence and label to the lists
    sequences[i] = sequence_data
    labels[i] = label_data
    if i % 100000 == 0:
        print(f"{i}")
    #i += seq_length

X = np.array(sequences)
y = np.array(labels)

X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)
output_length = prediction_horizon - 1
# Build the model
model = Sequential()
model.add(LSTM(64, activation='sigmoid', input_shape=(seq_length, X.shape[2])))
model.add(Dropout(0.5))
model.add(Dense(32, activation='sigmoid', kernel_regularizer=l2(0.01)))
model.add(Dense(output_length, activation='linear'))  # Linear activation for regression

early_stopping = EarlyStopping(monitor='val_loss', patience=5, restore_best_weights=True)
optimizer = tf.keras.optimizers.Adam(learning_rate=0.001, clipvalue=1)
model.compile(optimizer=optimizer, loss='mean_squared_error', metrics=['mae'])
history = model.fit(X_train, y_train, callbacks=[early_stopping], epochs=50, batch_size=32, validation_data=(X_test, y_test))
model.save('lstm_all_andrew_clean.h5')
plt.plot(history.history['loss'])
plt.grid(True)
plt.savefig('learning_all_andrew_clean.png')
plt.show()

predictions = model.predict(X_test)
print("STORM VALUES")
# predictions = scaler.inverse_transform(predictions)
# y_test_reverse = scaler.inverse_transform(y_test)
# accuracy = accuracy_score(y_test, predictions)
# print(f"Accuracy: {accuracy:.2f}")
dist = abs(max_norm_y - min_norm_y)
# inverted_predictions = scaler.inverse_transform(predictions)
# inverted_y_test = scaler.inverse_transform(y_test)
float_format = "{:.2f}"
inverse = scaler.inverse_transform(predictions) + min_y
with open("mean_dev_all_andrew_day_day_clean.csv", "w", newline = "") as file:
    writer = csv.writer(file)
    mean_dev_array = []
    for i in range(len(predictions)):
        if min_norm_storm_y < y_test[i][0] < max_norm_storm_y:
        #if 200 < i < 300:
        #if True:
            dev_array = []
            for j in range(output_length):
                dev = (y_test[i][j] - predictions[i][j]) / dist * 100
                dev_array.append(dev)
            mean_dev = np.mean(dev_array)
            #print(str(i) + ': ' + str(predictions[i]) + ' ' + str(y_test[i]) + ' dev: ' + str(mean_dev) + '%')
            #print(str(i) + ': ' + str(scaler.inverse_transform(predictions) + min_y) + ' '  + ' dev: ' + str(mean_dev) + '%')
            mean_dev_array.append(mean_dev)
            # formatted_row = [float_format.format(value) for value in inverse[i]]
            # writer.writerow(formatted_row)
            writer.writerow([mean_dev])
            #print(mean_dev)
    print("=== RESULT ===")
    print(np.mean(mean_dev_array))
