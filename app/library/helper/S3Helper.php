<?php

namespace Helper;

use Phalcon\DI;

class S3Helper 
{

	public static function storePublic($type = "", $fileName = "", $contentType = "", $fileIsLocatedAt = "")
	{

		$s3Client = DI::getDefault()->get('S3Client');

		$key = $type . "/" . $fileName;

		try {
			$result = $s3Client->putObject([
	            'Bucket' => DI::getDefault()->get('config')->aws->bucket,
	            'Key'    => $key,
	            'SourceFile'   => $fileIsLocatedAt,
	            'ContentType'  => "$contentType",
	            'ACL'          => 'public-read',
	            'StorageClass' => 'STANDARD',
	        ]);
		} catch (\Aws\Exception\S3Exception $e) {
			error_log($e);
			return false;
		}
		
		return $result;
	}

	public static function store($type = "", $fileName = "", $contentType = "", $fileIsLocatedAt = "")
	{

		$s3Client = DI::getDefault()->get('S3Client');

		$key = $type . "/" . $fileName;

		try {
			$ServerSideEncryption = DI::getDefault()->get('config')->aws->ServerSideEncryption;
			$SSEKMSKeyId = DI::getDefault()->get('config')->aws->SSEKMSKeyId;

			$result = $s3Client->putObject([
	            'Bucket' => DI::getDefault()->get('config')->aws->bucket,
	            'Key'    => $key,
	            'SourceFile'   => $fileIsLocatedAt,
	            'ContentType'  => "$contentType",
	            'ACL'          => 'private',
	            'StorageClass' => 'STANDARD',
				'ServerSideEncryption' => $ServerSideEncryption,
				'SSEKMSKeyId' => $SSEKMSKeyId,
	        ]);
		} catch (\Aws\Exception\S3Exception $e) {
			error_log($e);
			return false;
		}
		
		return $result;
	}

	public static function getUrl($key) {
		$s3Client = DI::getDefault()->get('S3Client');
		
		$bucket = DI::getDefault()->get('config')->aws->bucket;
		$ServerSideEncryption = DI::getDefault()->get('config')->aws->ServerSideEncryption;
		$SSEKMSKeyId = DI::getDefault()->get('config')->aws->SSEKMSKeyId;
		$ttl = DI::getDefault()->get('config')->aws->ttl;

		$cmd = $s3Client->getCommand('GetObject', [
		    'Bucket' => $bucket,
		    'Key'    => $key,
		    'ServerSideEncryption' => $ServerSideEncryption,
		    'SSEKMSKeyId' => $SSEKMSKeyId,
		]);

		$result = $s3Client->createPresignedRequest($cmd, $ttl);
		$presignedUrl = (string) $result->getUri();
		
		return $presignedUrl;
	}

	public static function getUrlPublic($key) {
		$s3Client = DI::getDefault()->get('S3Client');
		$plainUrl = $s3Client->getObjectUrl(DI::getDefault()->get('config')->aws->bucket, $key);
        return $plainUrl;
	}

	public static function copy($source, $target)
	{
		$s3Client = DI::getDefault()->get('S3Client');
		$plainUrl = $s3Client->copyObject([
				'Bucket' => DI::getDefault()->get('config')->aws->bucket,
	            'ACL'          => 'public-read',
	            'CopySource' => DI::getDefault()->get('config')->aws->bucket . "/" . $source,
	            'Key' => $target
			]);
        return $plainUrl;
	}

	public static function getLimitedObjectUrl($key)
	{
		$s3Client = DI::getDefault()->get('S3Client');
		$plainUrl = $s3Client->getObject([
				'Bucket' => DI::getDefault()->get('config')->aws->bucket,
	            'Key' => $key,
	            'ResponseExpires' => gmdate(DATE_RFC2822, time() + 3600),
			]);
        return $plainUrl;
	}

}