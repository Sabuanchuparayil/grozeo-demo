<?php
namespace App\Http\Repositories;

use Aws\S3\S3Client;
use Illuminate\Support\Facades\DB;

class StorageRepository
{
	protected $s3Details, $s3Client;
	public function __construct()
	{
		$this->s3Details = DB::table('s3_bucket')->first();
        $this->s3Client = new S3Client([
            'region'        => $this->s3Details->region,
            'version'       => 'latest',
            'credentials'   => array(
                'key'           => $this->s3Details->access_key,
                'secret'        => $this->s3Details->secretkey,
            )
        ]);
	}

	public function s3PutItem($fileHex, $filePath = "", $extension = "pdf", $type = "public-read")
	{
		try
		{
			$filename = "{$filePath}.{$extension}";
			$result = $this->s3Client->putObject([
				'Bucket'      => $this->s3Details->tobucket,
				'Key'         => $filename,
				'Body'        => $fileHex,
				'ACL'         => $type,
			]);

			$url = @$result['ObjectURL'] ?? false;
			return $url;
		}
		catch (\Exception $e)
        {
            info("StorageRepository s3PutItem() Error");info($e);
            return false;
        }
	}

	public function getFileExtension($fileHex)
	{
		try
		{
			$tempFile = tempnam(sys_get_temp_dir(), 'partner_labels');
			file_put_contents($tempFile, $fileHex);
			$mime = mime_content_type($tempFile);
			unlink($tempFile);
			$mimeMap = [
			    'application/pdf' 		=> 'pdf',
			    'image/jpeg'      		=> 'jpg',
			    'image/png'       		=> 'png',
			    'image/gif'       		=> 'gif',
			    'application/zip' 		=> 'zip'
			];
			$extension = @$mimeMap[$mime] ?? false;
			return $extension;
		}
		catch (\Exception $e)
        {
            info("StorageRepository getFileExtension() Error");info($e);
            return false;
        }
	}
}
