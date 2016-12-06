<?php
namespace Hinet\Flysystem\Qiniu;
// 引入鉴权类
use Qiniu\Auth;
// 引入上传类
use Qiniu\Storage\UploadManager;

use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use League\Flysystem\Util;
class QiniuAdapter extends AbstractAdapter
{
	protected $bucket;
    protected $auth;
    protected $manager;
    private $logger;
    public function __construct(Auth $auth, $bucket)
    {
        $this->auth = $auth;
        $this->bucket = $bucket;
        $this->manager = new UploadManager();
        $this->logger = LogFactory::getLogger('Qiniu');
    }
    public function getBucket()
    {
        return $this->bucket;
    }
    public function getLogger()
    {
        return $this->logger;
    }
    /**
     * Write a new file.
     *
     * @param string $path
     * @param string $contents
     * @param Config $config Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function write($path, $contents, Config $config)
    {
        return $this->upload($path, $contents, $config);
    }
    protected function upload($path, $content, Config $config){
    	// 生成上传 Token
    	$token = $this->auth->uploadToken($this->bucket);
    	list($response, $error) = $this->manager->putFile($token, $content, $path);
    	if ($error !== null) {
	        throw new \InvalidArgumentException($error);
	    } else {
	        var_dump($response);
	        return $this->normalizeResponse($response->metadata, $path);
	    }
    }
    /**
     * Normalize the object result array.
     *
     * @param array $response
     *
     * @return array
     */
    protected function normalizeResponse(array $response, $path = null)
    {
        $result = ['path' => $path ?: $this->removePathPrefix(null)];
        if (isset($response['date'])) {
            $result['timestamp'] = $response['date']->getTimestamp();
        }
        if (isset($response['lastModified'])) {
            $result['timestamp'] = $response['lastModified']->getTimestamp();
        }
        if (substr($result['path'], -1) === '/') {
            $result['type'] = 'dir';
            $result['path'] = rtrim($result['path'], '/');
            return $result;
        }
        return array_merge($result, Util::map($response, static::$resultMap), ['type' => 'file']);
    }
}