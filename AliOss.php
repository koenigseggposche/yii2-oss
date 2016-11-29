<?php
namespace koenigseggposche\yii2oss;

use Yii;
use OSS\OssClient;
use OSS\Core\OssException;
use yii\base\Component;

class AliOss extends Component
{
    public $prefix = '';
    public $bucket = '';
    public $AccessKeyId = '';
    public $AccessKeySecret = '';
    public $domain = '';
    public $imageHost = '';
    public $endPoint = 'http://oss-cn-beijing.aliyuncs.com';

    private $client;
    public function init()
    {
        try {
            $this->client = new OssClient($this->AccessKeyId, $this->AccessKeySecret, $this->endPoint);
        } catch (OssException $e) {
            Yii::error($e->getErrorCode() . ': ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 上传文件到OSS
     * @param $path
     * @param null $object
     * @param null $bucket
     * @param null $prefix
     */
    public function uploadFile($path, $object = null, $prefix = null, $bucket = null)
    {
        try {
            $content = file_get_contents($path);
        } catch (\Exception $e) {
            Yii::error($e->getMessage());
            throw $e;
        }
        return $this->uploadData($content, $object, $prefix, $bucket);
    }

    /**
     * 上传文件流到OSS
     * @param $content
     * @param null $object
     * @param null $prefix
     * @param null $bucket
     */
    public function uploadData($content, $object = null, $prefix = null, $bucket = null)
    {
        try {
            if (is_null($object)) {
                $object = date('YmdHis') . mb_substr(md5($content), -8);
            }
            $prefix = $prefix ?: $this->prefix;
            $bucket = $bucket ?: $this->bucket;
            $object = $prefix ? "$prefix/$object" : $object;
            return $this->client->putObject($bucket, $object, $content);
        } catch (OssException $e) {
            Yii::error($e->getErrorCode() . ': ' . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            Yii::error($e->getMessage());
            throw $e;
        }
    }

    /**
     * 删除文件
     * @param $object
     * @param null $prefix
     * @param null $bucket
     * @throws OssException
     */
    public function delete($object, $prefix = null, $bucket = null)
    {
        try {
            $prefix = $prefix ?: $this->prefix;
            $bucket = $bucket ?: $this->bucket;
            $object = $prefix ? "$prefix/$object" : $object;
            $this->client->deleteObject($bucket, $object);
        } catch (OssException $e) {
            Yii::error($e->getErrorCode() . ': ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 下载文件
     * @param string $object
     * @param null $prefix
     * @param null $bucket
     * @return string
     * @throws OssException
     */
    public function downloadData($object, $prefix = null, $bucket = null)
    {
        try {
            $prefix = $prefix ?: $this->prefix;
            $bucket = $bucket ?: $this->bucket;
            $object = $prefix ? "$prefix/$object" : $object;
            return $this->client->getObject($bucket, $object);
        } catch (OssException $e) {
            Yii::error($e->getErrorCode() . ': ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 代理方法
     * @param string $method_name
     * @param array $args
     * @return mixed
     */
    public function __call($method_name, $args)
    {
        return call_user_func_array([$this->client, $method_name], $args);
    }
}
