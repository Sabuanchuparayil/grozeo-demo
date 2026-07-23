<?php

namespace BackOffice\Http\Repositories\RelationOfficer;

use Illuminate\Support\Facades\DB;

class AwsBucketPresigned
{
    public function getBucketLink()
    {
        $s3Details = DB::table('s3_bucket')->first();

        $s3Client = new \Aws\S3\S3Client([
            'region'        => $s3Details->region,
            'version'       => 'latest',
            'credentials'   => array(
                'key'           => $s3Details->access_key,
                'secret'        => $s3Details->secretkey,
            )
        ]);
        $uuid = DB::select('SELECT UUID() as uuid')[0]->uuid;
        $cmd = $s3Client->getCommand('PutObject', [
            'Bucket'    => $s3Details->tobucket,
            'Key'       => "crm_contacts/{$uuid}.jpg",
            'ACL'       => 'public-read'
        ]);

        $request = $s3Client->createPresignedRequest($cmd, '+20 minutes');

        // Get the actual presigned-url
        return (string) $request->getUri();
    }
}