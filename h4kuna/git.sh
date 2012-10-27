#!/bin/bash

STATUS='status'

case $1 in
     --pull) STATUS='status';;
     --push) STATUS='push';;
esac

cd `dirname "$0"`
repositories=('curl' 'date-time' 'exchange' 'experimental' 'file' 'fio' 'gettext-latte' 'iterators' 'mutex' 'number-format' 'object-wrapper' 'static' 'tests' 'unit-conversion' )


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

