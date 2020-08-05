<?php
namespace Dapatchi\LaravelCore\Services;

use Dapatchi\LaravelCore\Helpers\StringHelper;
use Aws\S3\PostObjectV4;
use Aws\S3\S3Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class S3UploadService
{
    /**
     * @var S3Client
     */
    protected $s3Client;

    /**
     * S3UploadPolicyService constructor.
     * @param S3Client $s3Client
     */
    public function __construct(S3Client $s3Client)
    {
        $this->s3Client = $s3Client;
    }

    /**
     * @param string $bucketName
     * @param string $filename
     * @param int $expiryInSeconds
     * @param string $acl
     * @param string|null $contentType
     * @param array $metadataInput
     * @return array
     */
    public function generatePolicy(
        string $bucketName,
        string $filename,
        int $expiryInSeconds,
        string $acl,
        ?string $contentType = null,
        array $metadataInput = []
    ) {
        $options = [
            ['key' => $filename],
            ['acl' => $acl],
            ['bucket' => $bucketName],
        ];

        if ($contentType) {
            $options[] = ['content-type' => $contentType];
        }

        $metadataOptions = [];
        foreach ($metadataInput as $key => $value) {
            $metadataOptions[] = ['x-amz-meta-' . Str::kebab($key) => $value];
        }

        $postObject = new PostObjectV4(
            $this->s3Client,
            $bucketName,
            [],
            array_merge($options, $metadataOptions),
            '+' . $expiryInSeconds . ' seconds'
        );

        $policy = $postObject->getFormInputs();
        $policy['key'] = $filename;
        $policy['acl'] = $acl;

        $url = Arr::get($postObject->getFormAttributes(), 'action') . '/' . $filename;
        $url = StringHelper::deslashUrl($url);

        return [
            'key' => $filename,
            'url' => $url,
            'policy' => array_merge($policy, Arr::collapse($metadataOptions)),
            'form' => $postObject->getFormAttributes(),
        ];
    }

    /**
     * @param string $bucket
     * @param string $s3Key
     * @param string $filename
     * @param string $expiry
     * @return string
     */
    public function generateSignedUrl(string $bucket, string $s3Key, string $filename, string $expiry)
    {
        $command = $this->s3Client->getCommand('GetObject', [
            'Bucket' => $bucket,
            'Key' => $s3Key,
            'ResponseContentDisposition' => 'attachment; filename ="' . $filename . '"',
        ]);

        $request = $this->s3Client->createPresignedRequest($command, $expiry);

        return (string)$request->getUri();
    }
}
