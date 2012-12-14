#!/bin/bash

STATUS='status'

case $1 in
     --pull) STATUS='pull';;
     --push) STATUS='push';;
esac

cd `dirname "$0"`
<<<<<<< HEAD
repositories=('curl' 'data-type' 'date-time' 'exchange' 'experimental' 'file' 'fio' 'gettext-latte' 'iterators' 'mutex' 'number-format' 'object-wrapper' 'static' 'tests' 'unit-conversion' )
=======
repositories=('ares' 'curl' 'data-type' 'date-time' 'exchange' 'experimental' 'file' 'fio' 'gettext-latte' 'iterators' 'mutex' 'number-format' 'object-wrapper' 'static' 'tests' 'unit-conversion' )
>>>>>>> bfec671c47e7e71f8f3dc47ac72ac4f6d98828a1


for dir in ${repositories[*]}
do
    echo
    echo "$dir:"
    if [ -d $dir ]
    then
        cd $dir
        git $STATUS
        cd ..
    else
        git clone "git@github.com:h4kuna/$dir.git"
    fi
done

