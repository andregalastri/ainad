#!/bin/bash

workdir='extract'
gst='gtk.gresource'

mkdir -p 'extract/org/gnome/arc-theme/assets'

for r in `gresource list $gst`; do
    gresource extract $gst $r >$workdir$r
done