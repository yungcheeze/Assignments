Necessary Packages are listed in requirements.txt.
These can installed via:
      pip install -r path/to/requirements.txt

You also need to download the spacy english database.
It's needed for the stopwords and word vectors.
Once spacy is installed, this can be done via:
     python -m spacy download en_core_web_sm


DISCLAIMER:
In order to speed up the demonstration, the demos are only ran on a small
subset of the data. In addition, the neural nets are built from scratch
and only trained for 3 epochs. As a result of both these factors the results
will not be as good as those achieved in the report.
