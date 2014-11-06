#!/bin/bash

RELEASEDIR=`dirname $0`/releases/
PLUGINSRCDIR=`echo "$1" | sed "s|/\$||"`

if [ "$1" == ALL ]; then
    for plugin in `find . -name version.xml`; do
        $0 `dirname $plugin`
    done
    exit 0
fi

if [ ! -d $PLUGINSRCDIR ]; then
    echo "First argument must be a directory."
    exit 1
fi

gnutar() {
    if hash gtar 2>/dev/null; then
        gtar "$@"
    else
        tar "$@"
    fi
}


get_tag_content() {
    cat $2 |
    tr '\n' ' ' | 
    sed -e "s|.*\<${1}\>[ \t]*||g" -e  "s|[ \t]*\<\/${1}\>.*||g" 
}

versionfile=$PLUGINSRCDIR/version.xml
srcdir=$PLUGINSRCDIR

if [ ! -f $versionfile ]; then
    echo "Version file $versionfile not existing."
    exit 1
fi

plugin_name=`get_tag_content application "$versionfile"`
plugin_release=`get_tag_content release "$versionfile"`


if [ ! -d $RELEASEDIR ]; then
    mkdir -p $RELEASEDIR > /dev/null 2>&1
    if [ $? -ne 0 ]; then
        echo "Could not create '$RELEASEDIR' directory" 1>&2
        exit 1
    fi
fi

gnutar --xform="s|^$srcdir|$plugin_name|g" --xform="s|\.template$||g" -czf $RELEASEDIR/"$plugin_name-$plugin_release.tar.gz" "$srcdir"