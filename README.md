## Simple Photo Browser

Using a bootstrap theme (http://getboostrap.com) which can easily by alternative bootstrap themes, simple photo browser is a PHP web based application to browse photographs dumped into a specified folder on your website.  Thumbnails have to have been created and stored in the configured thumbnail (default thumbs) directory allowing you to decide what size thumbnail you want.  Configured to work out the box, it is possible to tweak a small amount of options.  The application reads both EXIF and IPTC data in the photographs hence no need for database or seperate text files decribing the photographs.

I use this application by orgainsing my photographs on my laptop (backed up daily), adding metadata - such as GPS Locations, IPTC data etc.  I then generate the thumbnails in the format I require and then sync the whole lot with the photo folder on my webserver.  You can view http://www.benjyellis.net/photos/.

As an added bonus, any requests for non existant or moved content is redirected to the home page with a 301 (permanant redirect) so bookmarks can be updated.

## Motivation

Many of the photo album applications on the web require too much work to work.  Obviously in order to appeal to a wider audience, these applications do have to have some complexity.  I intend to do a lot of travelling in the future, so I wanted something, where I can get my photos off the camera, organise them and add metadata - Location, descrition, GPS data etc locally, organise them into folders and just upload (using rsync) to the webserver.  Any changes I make to the local folders - e.g. move photos around are reflected on the webserver.  Simplez!!!

## Installation

Download the zip and either unzip locally and ftp to your webserver OR unzip on the webserver.  The .htaccess file is required to send all requests via the index.php script which will redirect any requests for non-existant files to the home page.

## Contributing

1. Fork it!
2. Create your feature branch: `git checkout -b my-new-feature`
3. Commit your changes: `git commit -am 'Add some feature'`
4. Push to the branch: `git push origin my-new-feature`
5. Submit a pull request :D

## Contributors

Just little o` me Benjamin Ellis - http://benjyellis.net

## License

Knock yourself out!!!! or in other words WTFPL - https://en.wikipedia.org/wiki/WTFPL
