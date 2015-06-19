#!/bin/bash

echo Creating sitemap...
echo http://www.benjyellis.net/photos/index.php > sitemap.txt;
find pictures/ -name '*.jpg' | grep -v 'thumbs' >> sitemap.txt;
sed -i 's#pictures#http://www.benjyellis.net/photos#' sitemap.txt;
sed -i 's#\.jpg#\.jpg\.html#' sitemap.txt;
gzip sitemap.txt;
echo Done.
