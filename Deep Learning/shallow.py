# main
# run_model_eval(pretrained_model=None)


# load data(data_dir)
# split data

# transform train data (override if pretrained)
# setup_model(test_data, override if pretrained)

# transform test data
# evaluate model(test_data)

#1 shallow model class
#specify model type


from sklearn.feature_extraction.text import CountVectorizer, TfidfVectorizer
from spacy.lang.en.stop_words import STOP_WORDS
from sklearn.naive_bayes import MultinomialNB
from sklearn import metrics


class ShallowClassifier(object):
    # TODO load pre-trained option
    def __init__(self, vectorization_method):
        if vectorization_method.upper() == "TF":
            self.vectorizer = CountVectorizer(stop_words=STOP_WORDS, lowercase=False, ngram_range=(1,2))
        elif vectorization_method.upper() == "TFIDF":
            self.vectorizer = TfidfVectorizer(stop_words=STOP_WORDS)

        self.model = MultinomialNB()

    def train_model(self, X_train, y_train):
        print("training model")
        x_train_dtm = self.vectorizer.fit_transform(X_train)
        self.model.fit(x_train_dtm, y_train)


    def evaluate_model(self, X_test, y_test):
        print("evaluating model")
        x_test_dtm = self.vectorizer.transform(X_test)
        y_pred = self.model.predict(x_test_dtm)
        print_metrics(y_test, y_pred)


def print_metrics(y_true, y_pred):
    acc = metrics.accuracy_score(y_true, y_pred)
    prec = metrics.precision_score(y_true, y_pred)
    rec = metrics.recall_score(y_true, y_pred)
    f1 = metrics.f1_score(y_true, y_pred)

    print("Accuracy: ", "{:.4f}".format(acc))
    print("Precision: ", "{:.4f}".format(prec))
    print("Recall: ", "{:.4f}".format(rec))
    print("F1: ", "{:.4f}".format(f1))
