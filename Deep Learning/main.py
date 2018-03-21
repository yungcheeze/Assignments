import pandas as pd
from sklearn.model_selection import train_test_split
from shallow import ShallowClassifier
from keras.models import load_model as keras_load_model
from deep import DeepClassifier
import os

# hide tensorflow warning messages
os.environ["TF_CPP_MIN_LOG_LEVEL"] = "3"
os.environ["PYTHONWARNINGS"] = "ignore"

# PATH VARIABLES
DATA_PATH = "./news_ds.csv"  # path of data csv file

data = pd.read_csv("news_ds.csv", header=0, delimiter=None)
data["CLEAN"] = data["TEXT"].str.replace("<[^<]+?>|\\n", "")  # remove html tags and carriage returns

X_train, X_test, y_train, y_test = train_test_split(data["CLEAN"],
                                                    data["LABEL"],
                                                    train_size=500,
                                                    test_size=50,
                                                    random_state=7)

def main():
    """
    Run demonstration of shallow model and deep model performance.
    DISCLAIMER:
    In order to speed up the demonstration, the demos are only ran on a small
    subset of the data. In addition, the neural nets are built from scratch
    and only trained for 3 epochs.
    As a result of both these factors the results will not be as good as those
    achieved in the report.
    """
    shallow_demo()
    deep_demo()

def shallow_demo():
    """
    Demonstrates Shallow classifier performance on train/test split defined at
    top of file
    """
    global X_train, X_test, y_train, y_test
    print("NAIVE BAYES WITH TF VECTORIZER")
    tf_classifier = ShallowClassifier("TF")
    tf_classifier.train_model(X_train, y_train)
    tf_classifier.evaluate_model(X_test, y_test)

    print("", "NAIVE BAYES WITH TFIDF VECTORIZER", sep="\n")
    tfidf_classifier = ShallowClassifier("TFIDF")
    tfidf_classifier.train_model(X_train, y_train)
    tfidf_classifier.evaluate_model(X_test, y_test)


def deep_demo():
    """
    Demonstrates Deep classifier performance on train/test split defined at
    top of file
    """
    global X_train, X_test, y_train, y_test
    print("", "VANILLA RNN MODEL", sep="\n")
    rnn_classifier = DeepClassifier("RNN")
    num_epochs = 3
    rnn_classifier.train_model(X_train, y_train, num_epochs)
    rnn_classifier.evaluate_model(X_test, y_test)

    print("", "LSTM MODEL", sep="\n")
    lstm_classifier = DeepClassifier("LSTM")
    num_epochs = 3
    lstm_classifier.train_model(X_train, y_train, num_epochs)
    lstm_classifier.evaluate_model(X_test, y_test)


if __name__ == "__main__":
    main()
