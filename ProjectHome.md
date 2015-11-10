Very simple to use PHP class.

### Usage example ###

include 'GoogleTranslate.php';

$t = new GogleTranslate;

//set input and output language:

$t->inLang = 'en';

$t->outLang = 'de';


//translate

echo $t->translate('Hello World');

//translate again, this time it will be loaded from cache!

echo $t->translate('Hello World');