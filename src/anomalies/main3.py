# %%
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

"""Things to be tuned: seq_length, stride, batch, learning_rate, and others..."""

X_data = pandas.read_csv("kyoto.csv", delimiter=',')
X_data = X_data.iloc[:35065]

y_data = pandas.read_csv("discovr_summed_all.csv", delimiter=',')


#################### MODEL PARAM ###################
IN_ACTIVATION = 'relu'
OUT_ACTIVATION = 'linear'
# optimizer
LEARNING_RATE = 0.001
# compile
LOSS='mean_squared_error'
# fit
EPOCHS = 50
BATCH_SIZE = 16
####################################################

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

    # x32
    for i in range(2):
        in_data = np.vstack((in_data, in_data))
        out_data = np.vstack((out_data, out_data))

    return np.nan_to_num(in_data), np.nan_to_num(out_data)


def print_is_nan(arr: np.array) -> None:
    has_nan = np.isnan(arr).any()

    if has_nan:
        print("The array contains NaN values.")
    else:
        print("The array does not contain NaN values.")


#################### MAIN ###################

X_data, y_data = transform(X_data, y_data)

mag_vector = y_data[:, :4]
seq_length = 20
X_data = np.hstack((mag_vector, X_data))

scaler = StandardScaler()
X_data = scaler.fit_transform(X_data)
y_data = scaler.fit_transform(y_data)

print_is_nan(X_data)
print_is_nan(y_data)

# Initialize empty lists to store sequences and labels
sequences = []
labels = []
stride = 5
size = len(X_data) - seq_length

for i in range(0, size, stride):

    in_seq = X_data[i: i + seq_length]
    out_seq = y_data[i, :]

    # Append the sequence and label to the lists
    sequences.append(in_seq)
    labels.append(out_seq)

    if i % 10000 == 0:
        print(f"{i}")

# Convert sequences and labels to NumPy arrays
X = np.array(sequences)
y = np.array(labels)

X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.3, random_state=42)
validation_split = 0.3  # You can adjust this percentage as needed
X_train, X_val, y_train, y_val = train_test_split(X_train, y_train, test_size=validation_split, random_state=42)


#################### Build the model ###################
model = Sequential()
model.add(LSTM(64, activation=IN_ACTIVATION, input_shape=(seq_length, X.shape[2])))
model.add(Dense(32, activation=IN_ACTIVATION, kernel_regularizer=l2(0.01)))
model.add(Dense(y_data.shape[1], activation=OUT_ACTIVATION))  # Linear activation for regression

print_is_nan(X_train)
print_is_nan(y_train)

#################### Train the model ###################
early_stopping = EarlyStopping(monitor='val_loss', patience=2, restore_best_weights=True)

optimizer = tf.keras.optimizers.Adam(learning_rate=LEARNING_RATE, clipvalue=1)
model.compile(optimizer=optimizer, loss=LOSS, metrics=['mean_absolute_error'])

history = model.fit(X_train, y_train, callbacks=[early_stopping], epochs=EPOCHS, batch_size=BATCH_SIZE,
                    validation_data=(X_val, y_val))

model.save('lstm_reverse_mag.keras')

plt.plot(history.history['loss'])
plt.grid(True)
plt.show()

model = load_model('lstm_reverse_mag.keras')

# Make predictions for storm level
predictions = model.predict(X_test)

plt.plot(y_test[:100, 4], label='real value')
plt.plot(predictions[:100, 4], label='prediction')
plt.text(2, 12, 'MSE: {:.2f}'.format(np.mean((y_test[:, 4] - predictions[:, 4]) ** 2)), fontsize=12, color='red')
plt.xlabel('Time')
plt.ylabel('A.U.')
plt.legend()
plt.title('Mean detectors value')
plt.show()

print(np.mean((y_test[:, 4] - predictions[:, 4]) ** 2))
plt.plot(y_test[:100, 5], label='real value')
plt.plot(predictions[:100, 5], label='prediction')
plt.text(2, 12, 'MSE:{}'.format(np.mean(y_test[:, 5] - predictions[:, 5]) ** 2, fontsize=12, color='red'))
plt.xlabel('Time')
plt.ylabel('A.U.')
plt.legend()
plt.title('Standard Deviation')
plt.show()

print(np.mean((y_test[:, 5] - predictions[:, 5]) ** 2))
plt.plot(y_test[:100, 6], label='real value')
plt.plot(predictions[:100, 6], label='prediction')
plt.text(2, 12, 'MSE:{}'.format(np.mean(y_test[:, 6] - predictions[:, 6]) ** 2, fontsize=12, color='red'))
plt.xlabel('Time')
plt.ylabel('Count')
plt.title('Zero detectors')
plt.legend()
plt.show()

print(np.mean((y_test[:, 6] - predictions[:, 6]) ** 2))
