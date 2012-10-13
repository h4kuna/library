#!/bin/bash

STATUS='pull'

case $1 in
     -s|--status) STATUS='status';;
esac

cd `dirname "$0"`
repositories=('gettext-latte' 'experimental' 'fio' 'iterators' 'static' 'object-wrapper' 'unit-conversion' 'date-time' 'curl' 'tests' 'exchange' 'number-format' 'mutex' 'file')


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

