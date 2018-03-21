import re
import numpy as np
import spacy
from spacy.lang.en.stop_words import STOP_WORDS
from keras.preprocessing.text import Tokenizer
from keras.preprocessing.sequence import pad_sequences
from keras.models import Sequential
from keras.layers import Dense
from keras.layers import LSTM
from keras.layers import SimpleRNN
from keras.layers import Dropout
from keras.layers.embeddings import Embedding
from shallow import print_metrics


def buildRNN(embedding_matrix):
    """
    Returns RNN model (used in DeepClassifier)
    """
    model = Sequential()
    embedding_layer = Embedding(embedding_matrix.shape[0],
                                embedding_matrix.shape[1],
                                weights=[embedding_matrix],
                                input_length=1000,
                                mask_zero=True,
                                trainable=False)
    model.add(embedding_layer)
    model.add(SimpleRNN(100, recurrent_dropout=0.2))
    model.add(Dense(1, activation="sigmoid"))
    model.compile(loss='binary_crossentropy',
                  optimizer='adam')
    return model


def buildLSTM(embedding_matrix):
    """
    Returns LSTM model (used in DeepClassifier)
    """
    model = Sequential()
    embedding_layer = Embedding(embedding_matrix.shape[0],
                                embedding_matrix.shape[1],
                                weights=[embedding_matrix],
                                input_length=1000,
                                mask_zero=True,
                                trainable=False)
    model.add(embedding_layer)
    model.add(LSTM(100, recurrent_dropout=0.2))
    model.add(Dense(1, activation="sigmoid"))
    model.compile(loss='binary_crossentropy',
                  optimizer='adam')
    return model


class DeepClassifier(object):

    def __init__(self, model_type, pretrained_model=None):
        if model_type.upper() == "RNN":
            self.build_function = buildRNN
        elif model_type.upper() == "LSTM":
            self.build_function = buildLSTM
        self.model = pretrained_model
        self.text_transformer = TextTransformer()

    def train_model(self, X_train, y_train, num_epochs=0):
        self.text_transformer.train_tokenizer(X_train)
        print("transforming_text...", end="")
        X_sequences = self.text_transformer.transform(X_train)
        print("done")
        if self.model is None:
            print("No model defined. Building one from scratch")
            vocab_size = self.text_transformer.tokenizer.num_words
            word_index = self.text_transformer.tokenizer.word_index
            print("generating embedding matrix...", end="")
            embedding_matrix = generate_embedding_matrix(vocab_size,
                                                         word_index)
            # embedding_matrix = np.load("embedding_mat.npy")
            print("done")
            print("building model...", end="")
            self.model = self.build_function(embedding_matrix)
            print("done")

        if num_epochs == 0:
            print("num_epochs set 0. Will not train neural net.")
        if num_epochs > 0:
            X_train = np.array([seq for seq in X_sequences])
            print("training for {} epochs".format(num_epochs))
            self.model.fit(X_train, y_train, epochs=num_epochs)
            print("done")

    def evaluate_model(self, X_test, y_test):
        print("predicting...", end="")
        X_sequences = self.text_transformer.transform(X_test)
        y_pred = self.model.predict(X_sequences)
        print("done")
        y_pred = np.round(y_pred)
        print_metrics(y_test, y_pred)


def generate_embedding_matrix(vocab_size, word_index):
    """
    Builds embedding matrix for embedding layer
    (used in DeepClassifier when generating model from scratch)

    PARAMS
    vocab_size: number of words to embed
    word_index: dictionary of vocab words to indices.
                All indices less than vocab_size are added to the
                embedding matrix
    """
    word2vec = WordVectorizer()
    embedding_matrix = np.zeros((vocab_size + 1, word2vec.dim))
    for word, i in word_index.items():
        if i > vocab_size:
            break
        embedding_vector = word2vec(word)
        if embedding_vector is not None:
            embedding_matrix[i] = embedding_vector
    return embedding_matrix


def filter_text(text):
    toks = re.findall(r"(?u)\b\w\w+\b", text)
    filtered = [tok for tok in toks if tok.lower() not in STOP_WORDS]
    return " ".join(filtered)


class TextTransformer(object):
    """
    Uses keras tokenizer to transform words into integer sequences.
    Lower integers/indices are given to words with higher document frequency.
    The document frequency is determined by passing a training sample to the
    train_tokenizer() function
    """
    def __init__(self):
        self.tokenizer = Tokenizer()

    def train_tokenizer(self, X_train):
        """
        Train tokenizer used to transform text to sequences
        """
        sparse = [filter_text(t) for t in X_train]
        self.tokenizer.fit_on_texts(sparse)
        token_count = len(self.tokenizer.word_index)
        self.tokenizer.num_words = int(0.1 * token_count)

    def transform(self, texts):
        """
        Transform texts to into padded sequences
        """
        sparse = [filter_text(t) for t in texts]
        sequences = self.tokenizer.texts_to_sequences(sparse)
        padded = [pad_sequences([s], maxlen=1000)[0] for s in sequences]
        return np.array(padded)


class WordVectorizer(object):
    """
    word2vec callable.
    takes string and returns word vector
    """
    def __init__(self):
        self.spacynlp = spacy.load('en_core_web_sm')
        self.dim = self.spacynlp("try").vector.shape[0]

    def __call__(self, word):
        vec = self.spacynlp(word).vector
        return vec
