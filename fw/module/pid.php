<?php
class pid {
    # https://stackoverflow.com/questions/4140552/how-to-detect-whether-a-php-script-is-already-running
    # https://www.php.net/manual/en/function.flock.php
    public function lock($file="") {
        if($file=="") { return FALSE; }
        $lock_file = fopen('/tmp/'.$file.'.pid', 'c');
        $got_lock = flock($lock_file, LOCK_EX | LOCK_NB, $wouldblock);
        if ($lock_file === false || (!$got_lock && !$wouldblock)) {
            throw new Exception(
                "Unexpected error opening or locking lock file. Perhaps you " .
                "don't  have permission to write to the lock file or its " .
                "containing directory?"
            );
        }
        else if (!$got_lock && $wouldblock) {
            //exit("Another instance is already running; terminating.\n");
            return FALSE;
        }
        // Lock acquired; let's write our PID to the lock file for the convenience
        // of humans who may wish to terminate the script.
        ftruncate($lock_file, 0);
        if (fwrite($lock_file, getmypid() . "\n")) {
            return $lock_file;
        }
        else { return FALSE; }
    }
    public function unlock($lock_file="") {
        if($lock_file=="") { return FALSE; }
        // All done; we blank the PID file and explicitly release the lock 
        // (although this should be unnecessary) before terminating.
        ftruncate($lock_file, 0);
        if (flock($lock_file, LOCK_UN)) {
            return TRUE;
        }
        else { return FALSE; }
    }
}
?>