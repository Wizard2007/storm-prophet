#%%
import numpy as np
import pandas as pd
from sklearn.ensemble import IsolationForest
from sklearn.svm import OneClassSVM
from sklearn.cluster import DBSCAN
from sklearn.preprocessing import StandardScaler
import matplotlib.pyplot as plt
import pandas
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
#, delimiter = ',', parse_dates=[0], infer_datetime_format=True, na_values='0', header = None


X_data_base = pandas.read_csv("kyoto.csv", delimiter=',')
X_data = X_data.iloc[35065:]

y_data = pandas.read_csv("discovr_summed_all.csv", delimiter=',')
#y_data = y_data.iloc[:35065]

#%%


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

from sklearn.preprocessing import MinMaxScaler


X_data, y_data = transform(X_data, y_data)
mag_vector = y_data[:, :4]
X_data = np.hstack((mag_vector, X_data))
seq_length = 20
#prediction_horizon = y_data.shape[1]
#output_length = seq_length + prediction_horizon - 1
# X_data = X_data.drop(X_data.columns[0], axis=1)
# y_data = y_data.drop(y_data.columns[0], axis=1)
#X_data = X_data.drop(X_data.columns[3:], axis=1)
# min_y = np.min(X_data)
# if min_y < 0:
#     X_data += -1 * min_y + 1
   # storm_idx = np.where((y_data >= -100 - min_y) & (y_data <= -50 - min_y))[0]
scaler = StandardScaler()
X_data = scaler.fit_transform(X_data)
y_data = scaler.fit_transform(y_data)
# max_norm_y = np.max(y_data)
# min_norm_y = np.min(y_data)
# max_norm_storm_y = np.max(y_data[storm_idx])
# min_norm_storm_y = np.min(y_data[storm_idx])
# X_data = X_data.astype(np.float32)
# y_data = y_data.astype(np.float32)


has_nan = np.isnan(X_data).any()
if has_nan:
    print("The array contains NaN values.")
else:
    print("The array does not contain NaN values.")
has_nan = np.isnan(y_data).any()
if has_nan:
    print("The array contains NaN values.")
else:
    print("The array does not contain NaN values.")
# Initialize empty lists to store sequences and labels
sequences = []
labels = []
stride = 5
size = len(X_data) - seq_length
# sequences = [0] * size
# labels = [0] * size

for i in range(0, size, stride):
    
    in_seq = X_data[i : i + seq_length]

    out_seq = y_data[i, :]
    # Append the sequence and label to the lists
    sequences.append(in_seq)
    labels.append(out_seq)
    if i % 10000 == 0:
        print(f"{i}")



# Convert sequences and labels to NumPy arrays
X = np.array(sequences)
y = np.array(labels)



X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.99, random_state=42)
# X_test, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)
validation_split = 0.5  # You can adjust this percentage as needed
X_train, X_val, y_train, y_val = train_test_split(X_train, y_train, test_size=validation_split, random_state=42)

# Build the model
# model = Sequential()
# #model.add(Masking(mask_value=np.nan, input_shape=(seq_length, X.shape[2])))
# model.add(LSTM(64, activation='sigmoid', input_shape=(seq_length, X.shape[2])))
# model.add(Dropout(0.5))
# model.add(Dense(32, activation='sigmoid', kernel_regularizer=l2(0.01)))
# #model.add(Dense(1, activation='linear'))  # Linear activation for regression
# model.add(Dense(y_data.shape[1], activation='linear'))  # Linear activation for regression

# # Train the model
# has_nan = np.isnan(X_train).any()
# if has_nan:
#     print("The array contains NaN values.")
# else:
#     print("The array does not contain NaN values.")
# has_nan = np.isnan(y_train).any()
# if has_nan:
#     print("The array contains NaN values.")
# else:
#     print("The array does not contain NaN values.")

# early_stopping = EarlyStopping(monitor='val_loss', patience=10, restore_best_weights=True)
# optimizer = tf.keras.optimizers.Adam(learning_rate=0.001, clipvalue=1)
# model.compile(optimizer=optimizer, loss='mean_squared_error', metrics=['mean_absolute_error'])
# history = model.fit(X_train, y_train, callbacks=[early_stopping], epochs=200, batch_size=32, validation_data=(X_val, y_val))
# model.save('lstm_reverse_mag.h5')
# plt.plot(history.history['loss'])
# plt.grid(True)
# plt.show()
model = load_model("lstm_reverse_mag.keras")
# Make predictions for storm level
predictions = model.predict(X_test)

# print("STORM VALUES")
# predictions = scaler.inverse_transform(predictions)
# y_test_reverse = scaler.inverse_transform(y_test)
# accuracy = accuracy_score(y_test, predictions)
# print(f"Accuracy: {accuracy:.2f}")
# dist = abs(max_norm_y - min_norm_y)
# inverted_predictions = scaler.inverse_transform(predictions)
# inverted_y_test = scaler.inverse_transform(y_test)
# float_format = "{:.2f}"

# print("TEST VALUES")
# input_data_4_hours = X_test[200:256]  # Replace with your input data for 4 hours ahead
#
# predictions_4_hour = []
#
# for _ in range(56):  # Assuming 1 day = 24 hours
#     prediction = model.predict(np.expand_dims(input_data_4_hours[-seq_length:], axis=0))[0]
#     predictions_4_hour.append(prediction)
#
# # Convert prediction lists to NumPy arrays
# predictions_4_hour = np.array(predictions_4_hour)
# for i in range(len(predictions_4_hour)):
#     #dev = (y_test[i] - predictions_4_hour[i]) / dist * 100
#     print(str(i) + ': ' + str(predictions_4_hour) + ' ' + str(y_test[i]) )

# predictions = scaler.inverse_transform(predictions)
# y_test_reverse = scaler.inverse_transform(y_test)
# accuracy = accuracy_score(y_test, predictions)
# print(f"Accuracy: {accuracy:.2f}")

# inverse = scaler.inverse_transform(predictions) + min_y
# with open("mean_dev_2020_all_andrew_4_12.csv", "w", newline = "") as file:
#     writer = csv.writer(file)
#     for i in range(len(predictions)):
#         #if min_norm_storm_y < y_test[i][0] < max_norm_storm_y:
#         #if 200 < i < 300:
#         if True:
#             dev_array = []
#             for j in range(output_length):
#                 dev = (y_test[i][j] - predictions[i][j]) / dist * 100
#                 dev_array.append(dev)
#             mean_dev = np.mean(dev_array)
#             print(str(i) + ': ' + str(predictions[i]) + ' ' + str(y_test[i]) + ' dev: ' + str(mean_dev) + '%')
#             #print(str(i) + ': ' + str(scaler.inverse_transform(predictions) + min_y) + ' '  + ' dev: ' + str(mean_dev) + '%')

#             # formatted_row = [float_format.format(value) for value in inverse[i]]
#             # writer.writerow(formatted_row)
#             writer.writerow([mean_dev])
#             #print(mean_dev)



plt.plot(y_test[:100, 4], label='real value')
plt.plot(predictions[:100, 4], label='prediction')
plt.text(2, 12, 'MSE: {:.2f}'.format(np.mean((y_test[:, 4] - predictions[:, 4])**2)), fontsize=12, color='red')
plt.xlabel('Time')
plt.ylabel('A.U.')
plt.legend()
plt.title('Mean detectors value')
plt.show()
print(np.mean((y_test[:, 4] - predictions[:, 4])**2))
plt.plot(y_test[:100, 5], label='real value')
plt.plot(predictions[:100, 5], label='prediction')
plt.text(2, 12, 'MSE:{}'.format(np.mean((y_test[:, 5] - predictions[:, 5])**2)), fontsize=12, color='red')
plt.xlabel('Time')
plt.ylabel('A.U.')
plt.legend()
plt.title('Standard Deviation')
plt.show()
print(np.mean((y_test[:, 5] - predictions[:, 5])**2))
plt.plot(y_test[:100, 6], label='real value')
plt.plot(predictions[:100, 6], label='prediction')
plt.text(2, 12, 'MSE:{}'.format(np.mean(y_test[:, 6] - predictions[:, 6])**2, fontsize=12, color='red'))
plt.xlabel('Time')
plt.ylabel('Count')
plt.title('Zero detectors')
plt.legend()
plt.show()
print(np.mean((y_test[:, 6] - predictions[:, 6])**2))

# dev_array = []
# for i in range(X_data):
#     dev = (y_test[i][j] - predictions[i][j]) / dist * 100
#     dev_array.append(dev)
# mean_dev = np.mean(dev_array)
# print(str(i) + ': ' + str(predictions[i]) + ' ' + str(y_test[i]) + ' dev: ' + str(mean_dev) + '%')
#print(str(i) + ': ' + str(scaler.inverse_transform(predictions) + min_y) + ' '  + ' dev: ' + str(mean_dev) + '%')

# formatted_row = [float_format.format(value) for value in inverse[i]]
# writer.writerow(formatted_row)
# writer.writerow([mean_dev])
#print(mean_dev)


