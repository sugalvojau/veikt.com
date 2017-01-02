<?php
/**
 * Created by PhpStorm.
 * User: Paulius
 * Date: 2016-10-08
 * Time: 21:33
 */

namespace DownloadCore;

use DownloadProject\Cvbankas\Lt\Classes\Job;
use Goutte\Client as GoutteClient;
use GuzzleHttp\Client as GuzzleClient;

/**
 * @TODO: Rename to "Worker" or something close to that?
 * The main reason to rename - the "uniqueBrowserId" for every run.
 * ...which is actually "uniqueWorkerId" or "uniqueCrawlingId" or...
 *
 * Class Browser
 * @package DownloadCore
 */
class Browser
{

    const HOMEPAGE_URL = null;
    const RETRY_TIMES = 10;
    const RETRY_TIMES_MIN_DELAY_IN_SECONDS = 3;
    const RETRY_TIMES_MAX_DELAY_IN_SECONDS = 10;
    const START_MIN_DELAY_IN_SECONDS = 1; // When starting this->doRepeatableAction method
    const START_MAX_DELAY_IN_SECONDS = 5; // When starting this->doRepeatableAction method

    // http://php.net/manual/en/function.curl-setopt.php
    const CURLOPT_TIMEOUT = 900;        // 15 min
    const CURLOPT_CONNECTTIMEOUT = 900; // 15 min
    // CURLOPT_LOW_SPEED_LIMIT = 1
    // CURLOPT_LOW_SPEED_TIME = 3600
    // CURLOPT_TIMEOUT = 3600
    // etc?

    protected $id;                  // Unique id of the Browser object.
    protected $projectSettings;
    protected $baseDir;
    protected $settings;
    protected $jobProperties;
    protected $blackListCharsForPageNumbers = array('-', '/', '|', ' ', "\n", "\r", '.');
    protected $jobsCounter;
    protected $robotsTxtContent;    // https://en.wikipedia.org/wiki/Robots_exclusion_standard

    public function __construct($baseDir, $settings, $projectSettings, $careAboutRobotsDotTxt = true)
    {

        // UNIQUE AUDITOR ID
        $reflect = new \ReflectionClass($this);
        $this->id = uniqid($reflect->getShortName(), true);

        // JOB COUNTER set to 0
        // It means: No jobs found by Browser at the time of creating Browser object
        $this->jobsCounter = 0;

        // PROJECT SETTINGS
        $this->projectSettings = $projectSettings;

        // BASE DIR
        $this->baseDir = $baseDir;

        // SETTINGS
        $this->settings = $settings;

        // REQUIRED PROPERTIES/FILES
        $this->jobProperties = $this->getJobProperties();

        // Should we take care about robots.txt file?
        if ($careAboutRobotsDotTxt) {
            $this->robotsTxtContent = $this->getRobotsTxtContent();
        } else {
            $this->robotsTxtContent = null;
        }

        // Datetime (UTC)
        $this->browserUtcDateTimeBegin = new \DateTime('now', new \DateTimeZone('UTC'));

    }

    protected function getJobProperties()
    {

        if (!isset($this->jobProperties)) {
            $this->jobProperties = (array)$this->settings['files-to-output'];
        }

        return $this->jobProperties;

    }

    protected function getRobotsTxtContent()
    {

        if (isset($this->robotsTxtContent)) {
            return $this->robotsTxtContent;
        }

        $url = $this->getRobotsTxtUrl();
        if (!$url) {
            $this->robotsTxtContent = '';
            return '';
        }

        $Content = $this->doRepeatableAction('getContentOfRemoteRobotsTxt', $url);
        if (!$Content) {
            $this->robotsTxtContent = '';
            return '';
        }

        $this->robotsTxtContent = $Content;
        return $this->robotsTxtContent;

    }

    protected function getRobotsTxtUrl()
    {

        $urlParsed = parse_url($this->projectSettings['url']);
        $url = ''
            . (isset($urlParsed['scheme']) ? ($urlParsed['scheme'] . '://') : '')
            . (
            (isset($urlParsed['user']) && isset($urlParsed['user']))
                ? ($urlParsed['pass'] . ':' . $urlParsed['pass'] . '@')
                : ''
            )
            . (isset($urlParsed['host']) ? $urlParsed['host'] : '')
            . (isset($urlParsed['port']) ? (':' . $urlParsed['port']) : '')
            . '/'
            . 'robots.txt';

        return $url;

    }

    /**
     * Calls $this->$methodName($argN) method.
     * Repeats the call
     *  for $this::RETRY_TIMES times
     *  if $this->$methodName returns NULL or FALSE
     * Every later call sleeps for a random amount of seconds between
     *  $this::RETRY_TIMES_MIN_DELAY_IN_SECONDS
     *  and
     *  $this::RETRY_TIMES_MAX_DELAY_IN_SECONDS
     *
     * @todo: This one definitely requires the test to be written
     *
     * @param string $methodName Name of method in $this class
     * @return mixed|null               Returned value of $this->$methodName method
     *
     */
    protected function doRepeatableAction($methodName)
    {

        if (!method_exists($this, $methodName)) {
            return null;
        }

        // Wait before starting
        sleep(rand($this::START_MIN_DELAY_IN_SECONDS, $this::START_MAX_DELAY_IN_SECONDS));

        $retryTimes = $this::RETRY_TIMES;
        if ((int)$retryTimes < 0) { // be secured!
            return null;
        }

        // Get arguments passed
        $args = func_get_args();
        // Skip $methodName
        array_shift($args);

        // Do action
        while ($retryTimes) {

            if ($this::RETRY_TIMES != $retryTimes) { // Not the first time?
                //var_dump('Sleeping for ' . $this::RETRY_TIMES_MIN_DELAY_IN_SECONDS . ', ' . $this::RETRY_TIMES_MAX_DELAY_IN_SECONDS);
                sleep(rand($this::RETRY_TIMES_MIN_DELAY_IN_SECONDS, $this::RETRY_TIMES_MAX_DELAY_IN_SECONDS)); // Wait
            }

            //var_dump('CALLING "' . $methodName . '"". Try #' . ($this::RETRY_TIMES - $retryTimes +1));
            $result = call_user_func_array(array($this, $methodName), $args);

            //var_dump('Result: ');
            //var_dump($result);

            // If result is strictly NULL or FALSE then it means "NO SUCCESS"
            if (isset($result) && ($result !== false)) {
                return $result;
            }

            $retryTimes--;
        }

        // Result may be FALSE or NULL
        // If result is FALSE...
        if (isset($result)) {
            return $result;
        }

        // If result is NULL or
        // if any surprises because of always possible bugs in a code...
        return null;

    }

    public function markQueueBegin()
    {
        $this->createQueueMarkerFile($this->settings['markers']['begin']['file-name']);
    }

    /**
     * @param $markerFileName
     */
    protected function createQueueMarkerFile($markerFileName)
    {
        $jobsPath = $this->getDownloadsDirectoryPathJobs();
        $this->createDirectoryIfNotExist($jobsPath);
        $this->createFileIfNotExists(
            $jobsPath . DIRECTORY_SEPARATOR . $markerFileName,
            $this->getMarkerContent()
        );
    }

    /**
     * @param \DateTime $DateTime
     * @return string
     */
    public function getDownloadsDirectoryPathJobs()
    {
        $dir = $this->baseDir
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . $this->projectSettings['dir_downloaded_posts']
            . DIRECTORY_SEPARATOR . $this->browserUtcDateTimeBegin->format('Y-m-d')
            . '--' . $this->getId();
        return $dir;
    }

    protected function getId()
    {
        return $this->id;
    }

    /**
     * @param $dir
     * @param int $cmod
     * @return bool
     */
    protected function createDirectoryIfNotExist($dir, $cmod = 0775)
    {
        if (!is_dir($dir)) {
            return mkdir($dir, $cmod, true);
        }
        return true;
    }

    private function createFileIfNotExists($fileFullPath, $fileContent)
    {
        return file_put_contents($fileFullPath, $fileContent);
    }

    protected function getMarkerContent()
    {

        $currentDateTime = new \DateTime('now', new \DateTimeZone('UTC'));

        return
            $this->getId()
            . "\n"
            . $currentDateTime->format('Y-m-d H:i:s');
    }

    public function markQueueEnd()
    {
        $this->createQueueMarkerFile($this->settings['markers']['end']['file-name']);
    }

    public function getJobsFromTheList($List)
    {

        if (!$List) {
            return array();
        }

        $jobs = array();

        foreach ($List as $url) {
            $Content = $this->doRepeatableAction('getContentOfUrl', $url);
            $jobs[$url] = $this->extractJob(
                $Content,
                $url
            );
        }

        return $jobs;

    }

    /**
     * @param \Symfony\Component\DomCrawler\Crawler $Content
     * @param array $filesRequiredToOutput
     * @param $url
     * @return
     */
    public function extractJob(
        \Symfony\Component\DomCrawler\Crawler $Content,
        $url
    )
    {

        if (empty($this->jobProperties)) {
            return null;
        }

        return new Job($Content, $this->jobProperties, $url, $this->projectSettings, $this->getId());

    }

    public function saveJobsToFiles(array $Jobs)
    {

        $result = array('success' => [], 'failure' => []);

        foreach ($Jobs as $url => $Job) {
            // Counter
            $counter = ++$this->jobsCounter;

            $dir = $this->createJobDirectoryIfNotExists($counter);

            $JobSaved = new JobSaved($url, $dir, $Job);

            if (!$dir) {
                $result['failure'][$url] = $JobSaved;
                continue;
            }

            $success = $this->saveJobToFile($Job, $dir);
            if (!$success) {
                $result['failure'][$url] = $JobSaved;
                continue;
            }

            $result['success'][$url] = $JobSaved;

        }

        return $result;

    }

    protected function createJobDirectoryIfNotExists($jobCounter)
    {

        if (empty($this->projectSettings)) {
            return false;
        }

        if (!isset($this->projectSettings['dir_downloaded_posts']) || empty($this->projectSettings['dir_downloaded_posts'])) {
            return false;
        }

        if (!isset($jobCounter) || empty($jobCounter)) {
            return false;
        }

        $dir = $this->getDownloadsDirectoryPathJob($jobCounter);
        $success = $this->createDirectoryIfNotExist($dir);

        if (!$success) {
            return false;
        }

        return $dir;

    }

    /**
     * @param $jobCounter
     * @return string
     */
    protected function getDownloadsDirectoryPathJob($jobCounter)
    {
        $dir = $this->getDownloadsDirectoryPathJobs()
            . DIRECTORY_SEPARATOR . $jobCounter
            . '--' . $this->browserUtcDateTimeBegin->format('Y-m-d--H-i-s')
            . '--' . uniqid();
        return $dir;
    }

    protected function saveJobToFile(\DownloadCore\Job $Job, $dirToSaveTo)
    {

        $success = true;

        foreach ($Job as $property => $value) {

            $file = $dirToSaveTo . DIRECTORY_SEPARATOR . $property;

            if (!$this->saveValueToFile($file, $value)) {
                $success = false;
            }
        }

        return $success;

    }

    protected function saveValueToFile($file, $content)
    {

        $fp = fopen($file, 'w');
        $success = fwrite($fp, $content);
        fclose($fp);

        if ($success === false) {
            return false;
        }

        return true;

    }

    public function getNextListOfJobLinks()
    {

        $nextPageUrl = $this->getNextPageUrlOfListOfJobLinks();
        $this->listContent = $this->doRepeatableAction('getContentOfUrl', $nextPageUrl);
        return (array)$this->extractJobLinks();

    }

    /**
     * To be implemented in child class
     *
     * @return null
     */
    protected function getNextPageUrlOfListOfJobLinks()
    {
        return null;
    }

    /**
     * Returns content of URL
     *
     * @param string $url Any valid URL
     * @param string $actionType "GET", "POST", any other...
     * @return null|\Symfony\Component\DomCrawler\Crawler
     */
    protected function getContentOfUrl($url, $actionType = 'GET', $listenRobotsDotTxt = true)
    {

        if (!$url) {
            return null;
        }

        // Check if url is allowed
        if ($listenRobotsDotTxt && $this->robotsTxtContent) {
            $parser = new \RobotsTxtParser($this->robotsTxtContent);
            // $parser->setUserAgent('VeiktDotComBot'); // ???
            if ($parser->isDisallowed($url)) {
                return null;
            }
        }

        $goutteClient = new GoutteClient();
        $guzzleClient = new GuzzleClient(array(
            'curl' => array(
                CURLOPT_TIMEOUT => $this::CURLOPT_TIMEOUT,
                CURLOPT_CONNECTTIMEOUT => $this::CURLOPT_CONNECTTIMEOUT,
            ),
        ));
        $goutteClient->setClient($guzzleClient);
        $result = $goutteClient->request($actionType, $url);

        if (!$result) {
            return null;
        }

        return $result;

    }

    /**
     * Gets content of remote rebots.txt file
     * @todo: merge with getContentUrl
     *
     * @param $url              For example: "http://www.example.org/robots.txt
     * @return null|string
     */
    protected function getContentOfRemoteRobotsTxt($url)
    {

        if (!$url) {
            return null;
        }

        $result = file_get_contents($url);

        if (!$result) {
            return null;
        }

        return $result;

    }

}