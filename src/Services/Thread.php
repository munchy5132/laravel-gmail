<?php

namespace Dacastro4\LaravelGmail\Services;

use Dacastro4\LaravelGmail\LaravelGmailClass;
use Dacastro4\LaravelGmail\Services\Thread\ThreadIndividual;
use Dacastro4\LaravelGmail\Traits\Filterable;
use Dacastro4\LaravelGmail\Traits\SendsParameters;
use Google\Exception;
use Google_Service_Gmail;
use Illuminate\Support\Collection;

class Thread
{

	use SendsParameters;

	public $service;

	public $preload = false;

	public $pageToken;

	public $client;

	/**
	 * Optional parameter for getting single and multiple emails
	 *
	 * @var array
	 */
	protected $params = [];

	/**
	 * Thread constructor.
	 *
	 * @param  LaravelGmailClass  $client
	 */
	public function __construct(LaravelGmailClass $client)
	{
		$this->client = $client;
		$this->service = new Google_Service_Gmail($client);
	}

	/**
	 * Returns next page if available of messages or an empty collection
	 *
	 * @return Collection
	 * @throws \Google_Exception
	 */
	public function next()
	{
		if ($this->pageToken) {
			return $this->all($this->pageToken);
		} else {
			return new ThreadCollection([], $this);
		}
	}

    /**
     * Returns a collection of Mail instances
     *
     * @param null|string $pageToken
     *
     * @return Collection
     * @throws Exception
     */
	public function all($pageToken = null)
	{
		if (!is_null($pageToken)) {
			$this->add($pageToken, 'pageToken');
		}

		$threads = [];
		$response = $this->getThreadResponse();
		$this->pageToken = method_exists( $response, 'getNextPageToken' ) ? $response->getNextPageToken() : null;

		$allThreads = $response->getThreads();

		if (!$this->preload) {
			foreach ($allThreads as $thread) {
				$threads[] = new ThreadIndividual($thread, $this->preload);
			}
		} else {
			$threads = $this->batchRequest($allThreads);
		}

		$all = new ThreadCollection($threads, $this);

		return $all;
	}

	/**
	 * Returns boolean if the page token variable is null or not
	 *
	 * @return bool
	 */
	public function hasNextPage()
	{
		return !!$this->pageToken;
	}

	/**
	 * Limit the messages coming from the queryxw
	 *
	 * @param  int  $number
	 *
	 * @return Thread
	 */
	public function take($number)
	{
		$this->params['maxResults'] = abs((int) $number);

		return $this;
	}

	/**
	 * @param $id
	 *
	 * @return ThreadIndividual
	 */
	public function get($id)
	{
        $thread = $this->getRequest($id);
		return new ThreadIndividual($thread);
	}

	/**
	 * Creates a batch request to get all threads in the mail.
	 *
	 * @param $allThreads
	 *
	 * @return array|null
	 */
	public function batchRequest($allThreads)
	{
		$this->client->setUseBatch(true);

		$batch = $this->service->createBatch();

		foreach ($allThreads as $key => $message) {
			$batch->add($this->getRequest($message->getId()), $key);
		}

		$threadBatch = $batch->execute();

		$this->client->setUseBatch(false);

		$threads = [];

		foreach ($threadBatch as $thread) {
			$threads[] = new ThreadIndividual($thread);
        }

		return $threads;
	}

	/**
	 * Preload the information on each Mail objects.
	 * If is not preload you will have to call the load method from the Mail class
	 * @return $this
	 * @see Mail::load()
	 *
	 */
	public function preload()
	{
		$this->preload = true;

		return $this;
	}

	public function getUser()
	{
		return $this->client->user();
	}

	/**
	 * @param $id
	 *
	 * @return \Google_Service_Gmail_Thread
	 */
	private function getRequest($id)
	{
		return $this->service->users_threads->get('me', $id);
	}

    /**
     * @return \Google_Service_Gmail_ListMessagesResponse|object
     * @throws Exception
     */
	private function getThreadResponse()
	{
		$responseOrRequest = $this->service->users_threads->listUsersThreads( 'me', $this->params );

		if ( get_class( $responseOrRequest ) === "GuzzleHttp\Psr7\Request" ) {
			$response = $this->service->getClient()->execute( $responseOrRequest, 'Google_Service_Gmail_ListThreadsResponse' );
			return $response;
		}

		return $responseOrRequest;
	}
}
