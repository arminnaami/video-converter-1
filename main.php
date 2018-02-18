#!/usr/local/bin/php
<?php

// Open an inotify instance
$inoInst = inotify_init();

// this is needed so inotify_read while operate in non blocking mode
stream_set_blocking($inoInst, 0);

$inputFolder = __DIR__ . '/input-bucket';
// watch for everything in our input-bucket folder
$inputBucketWatcher = inotify_add_watch($inoInst, $inputFolder, IN_CLOSE_WRITE | IN_MOVED_TO);

// add a secton watch to another directory, but only watch out create and delete.
$outputDir = __DIR__ . '/output-bucket';

$bitrate = 3000; // in K
$vCPU = 1; // num cpu cores to use

// not the best way but sufficient for this example :-)
while (true)
{
    // read events
    $events = inotify_read($inoInst);
    
    if ($events[0]['wd'] === $inputBucketWatcher)
    {
        if ($events[0]['mask'] === IN_CLOSE_WRITE || $events[0]['mask'] === IN_MOVED_TO)
        {
            $inputFilename = $events[0]['name'];
            $parts = explode(".", $inputFilename);
            $extension = $parts[count($parts)-1];
            $inputFile = "{$inputFolder}/{$inputFilename}";
            
            if (in_array($extension, array("mp4", "mkv", "webm", "flv", "mov", "avi")))
            {
                $outputFile = $outputDir . '/' . $inputFilename . '.x265.3000kbps.mkv';
                
                // Ensure unique filenames.
                while (file_exists($outputFile))
                {
                    $outputFile = $outputDir . '/' . $inputFilename . ".x265.3000kbps_" . time() . ".mkv";
                }
            
                # bitrate based
                $cmd = 
                    "/usr/bin/ffmpeg" .
                    ' -i "' . $inputFile . '"' .
                    ' -c:v libx265' . 
                    ' -b:v ' . $bitrate . 'K' .
                    ' -x265-params' . 
                    ' pass=1' .
                    ' -threads ' . $vCPU .
                    ' -preset medium' .
                    ' -c:a copy' .
                    ' -f mp4' . 
                    ' /dev/null -y';
                    
                $cmd .=  ' && ' .
                    "/usr/bin/ffmpeg" .
                    ' -i "' . $inputFile . '"' .
                    ' -c:v libx265' . 
                    ' -b:v ' . $bitrate . 'K' .
                    ' -x265-params' . 
                    ' pass=2' .
                    ' -threads ' . $vCPU .
                    ' -preset medium' .
                    ' -c:a copy' .
                    ' "' . $outputFile . '"';
                    
                print "Converting file: " . $events[0]['name'] . PHP_EOL;
                shell_exec($cmd);
                unlink($inputFile);
            }
            else
            {
                // did not recognize the file, just kick it through
                $outputFile = $outputDir . '/' . $inputFilename;
                
                // Ensure unique filenames.
                while (file_exists($outputFile))
                {
                    $outputFile = $outputDir . '/' . time() . '_' . $inputFilename;
                }
                
                rename($inputFile, $outputFile);
            }
        }
    }
    
    sleep(1);
}

// stop watching our directories
inotify_rm_watch($inoInst, $inputBucketWatcher);
inotify_rm_watch($inoInst, $outputBucketWatcher);

// close our inotify instance
fclose($inoInst);

