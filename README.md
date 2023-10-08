# NASA space app challenge 2023 team Storm-prophet

## The challenge 
When operating reliably, the National Oceanic and Atmospheric Administration’s (NOAA’s) space weather station, the Deep Space Climate Observatory (DSCOVR), can measure the strength and speed of the solar wind in space, which enables us to predict geomagnetic storms that can severely impact important systems like GPS and electrical power grids on Earth. DSCOVR, however, continues to operate past its expected lifetime and produces occasional faults that may themselves be indicators of space weather. Your challenge is to use the "raw" data from DSCOVR—faults and all—to predict geomagnetic storms on Earth.

## Structure 
- src/ml    
    AI LSTM model with data preperation alogorith to predist solar storm 
- src/anomalies    
    AI model to find anomalies in raw inpt data
- src/web
    web application for data wisualisation

# How to

This section provides an overview of how to use the provided Python code for time series prediction using LSTM (Long Short-Term Memory) neural networks. This code is designed to predict future Disturbance Storm Time (DST) by using raw data drom DSCOVR.

## Prerequisites

Before using this code, make sure you have the following installed:

- Python (recommended version 3.6+)
- TensorFlow (2.0 or later)
- Scikit-Learn
- Pandas
- Matplotlib

You can install these libraries using pip if you haven't already:

```bash
pip install tensorflow scikit-learn pandas matplotlib
```

LSTM is stored in file src/ml/lstm_andrew_clean.py

Please, use for input data files from Data source section. You need put then in the same forlder with lstm_andrew_clean.py file. 
Or put you data files and set full / relative path in be low sorce code lines. 

```puthon
X_data = pd.read_csv('discovr_summed_all.csv')
y_data = pd.read_csv('kyoto_minute_full.csv')
```

Note. Please, dtasource structure before put you datasets.

## Getting Started

Follow these steps to get started with the LSTM time series prediction code:

1. **Import Required Libraries**: At the beginning of your Python script, import the necessary libraries. This includes TensorFlow, scikit-learn, Pandas, Matplotlib, and other relevant modules. You can copy the import statements from the provided code.

2. **Data Preparation**: Prepare your historical data and the data you want to predict. The code assumes that your data is stored in CSV files. Replace the file paths in the code with the paths to your CSV files. Ensure that your historical data and prediction data are compatible and contain the necessary columns.

3. **Data Transformation**: The `transform` function in the code joins the historical and prediction data by time and performs some data preprocessing, such as handling missing values (NaN). Make sure to call this function with your data before proceeding.

4. **Data Scaling**: The code scales the input and output data using `StandardScaler`. If your data requires different preprocessing or scaling methods, you can modify this part of the code.

5. **Model Parameters**: Adjust the hyperparameters according to your specific problem. You can modify `seq_length` and `prediction_horizon` to change the input sequence length and prediction horizon. Adjust the architecture of the LSTM model, such as the number of layers and units, according to your problem's complexity.

6. **Training**: The code builds an LSTM model, compiles it, and trains it on the data. You can modify the model architecture, loss function, and training parameters (e.g., batch size, epochs) as needed. The training process is also visualized with a learning curve plot.

7. **Saving the Model**: After training, the model is saved to a file named `'lstm_all_andrew_clean.h5'`. You can change the file name if needed.

8. **Prediction**: The code uses the trained model to make predictions on the test data. You can replace the test data with your own data. The predictions are saved to a CSV file named `"mean_dev_all_andrew_day_day_clean.csv"`.

Now you're ready to use the LSTM time series prediction code for your specific dataset and problem. Customize the code as needed and explore different configurations to achieve the best results.


## Data Sources
- Input data source
   https://drive.google.com/file/d/1ESoYpMM8eBj88QCYszABzB24pXMhM4Pl/view?usp=sharing

- DsT data to detect solar shtorm (if it -50 > it's solar storm)
  https://drive.google.com/file/d/1XBRJmxDpiumPvpHtCPsMX9zKkijIiJGr/view?usp=sharing


## Links 
- Storm forecst web site :
https://www.spaceappschallenge.org/2023/challenges/develop-the-oracle-of-dscovr/

- Team page :
https://www.spaceappschallenge.org/2023/find-a-team/storm-prophet/  

- Chellenge descrioption :
https://www.spaceappschallenge.org/2023/challenges/develop-the-oracle-of-dscovr/

## Contacts

- Anastasiia Lukianenko
  https://www.linkedin.com/in/anastasiia-lukianenko-709394139/

  https://github.com/NastyaVicodin 

- Konstantin Kuzmichev
  https://www.linkedin.com/in/konstantin-kuzmichev-72723526/

- Yevhen Tatarynov
  https://www.linkedin.com/in/yevhen-tatarynov
