# Video Converter
This is a simple project to demonstrate how one can use PHP with inotify to automatically process items that get "dropped" into a "bucket". The processed items will appear in the "output-bucket".

At the moment it is sent to look out for mp4 and mkv files and convert them to h265 (HEVC) mkv video files with a bitrate of 3000K, but you could easily swap this logic out to do any kind of processing you want.

At the moment the processing happens like a queue, whereby it will work on one file at a time in the order that you dropped the files.

### Requirements
* [Install ffmpeg](https://blog.programster.org/ubuntu-16-04-install-ffmpeg-from-ppa)
* [Install PHP 7 with the inotify extension](https://blog.programster.org/ubuntu-16.04-install-PHP-inotify).

# Steps
* Clone this repository with `git clone https://github.com/programster/video-converter.git`.
* Execute the `main.php` script with `php main.php`;
* Drop some files into the input-bucket for processing.
* Wait for the video(s) to process. (It can take quite a while).
* Look at your converted videos in the output bucket.

## Roadmap
* At some point this project will be dockerized so that you don't have to worry about the requirements mentioned above.
* Add a third bucket for processing, which will contain the files as they are being worked on. Thus the file goes from input > processing > ouput.
* Control a fleet of "workers" that do the processing. A master will monitor the buckets and keep feeding any available workers files so that multiple files can be worked on in parallel, and processing can be scaled up by turning on more servers.
