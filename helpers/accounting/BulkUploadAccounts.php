<?php
//
namespace Ssentezo\Accounting;

use Ssentezo\Util\BulkUpload;
// use Ssentezo\Util\Download;
// die();
class BulkUploadAccounts extends BulkUpload
{
    // use Download;
    protected $fileName;
    // die();
    protected $data;
    protected static $fileHeadings = [
        "GL Code",
        "Account Name"
    ];
    protected $fileHeadingMappings = [
        "GL Code" => "code",
        "Account Name" => "name"
    ];
    private function loadData()
    {
        $this->data =  $this->fetchAssocFromCsvFile($this->fileName, $this->fileHeadingMappings);
    }
    public function getData()
    {
        $this->loadData();
        return $this->data;
    }
    public function migrateAccounts()
    {
        $this->loadData();
        foreach ($this->data as $key => $value) {
            $code = $value['code'];
            $name = $value['name'];
            $category = Account::categorize($code);
            $balance = 0;
            $account = new NewAccount($name, $category, $code, $balance);
        }
    }
    public static function createAccountsCsvFile()
    {

        BulkUpload::createCsvFile(static::$fileHeadings,"Add accounts bulk");
        // Download::createCsvFile(static::$fileHeadings, "Bulk Add accounts file");
    }
}
