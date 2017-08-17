#!/bin/bash

STATUS='status'

case $1 in
     --pull) STATUS='pull';;
     --push) STATUS='push';;
esac

cd `dirname "$0"`
repositories=( 'acl' 'assets' 'ares' 'database' 'data-type' 'exchange' 'exchange-nette' 'fio' 'fio-nette' 'gettext-latte' 'image-manager' 'iterators' 'latte-php-tokenizer' 'mail-manager' 'menu' 'number-format' 'object-wrapper' 'upload' )


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

