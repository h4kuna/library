#!/bin/bash

STATUS='status'

case $1 in
     --pull) STATUS='pull';;
     --push) STATUS='push';;
esac

cd `dirname "$0"`
repositories=('ares' 'database' 'data-type' 'date-time' 'exchange' 'fio' 'gettext-latte' 'image-manager' 'iterators' 'mail-manager'  'menu' 'mutex' 'number-format' 'object-wrapper' 'static' 'tests' 'unit-conversion' )


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

