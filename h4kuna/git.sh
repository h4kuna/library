#!/bin/bash

cd `dirname "$0"`
repositories=('gettext-latte' 'experimental' 'fio' 'iterators' 'static' 'object-wrapper' 'unit-conversion' 'date-time' 'curl' 'tests' 'exchange' 'number-format' 'mutex' 'file')


for dir in ${repositories[*]}
do
    if [ -d $dir ]
    then
        cd $dir
        git pull
        cd ..
    else
        git clone "git@github.com:h4kuna/$dir.git"
    fi
done

