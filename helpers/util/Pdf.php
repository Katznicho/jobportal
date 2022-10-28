<?php

namespace Ssentezo\Util;

use \Mpdf\Mpdf;

// SetTitle()
// SetAuthor()
// SetCreator()
// SetSubject()
// SetKeywords()

class Pdf
{
    private $mpdf;
    protected $name;
    protected $title;
    protected $author;
    protected $creator;
    protected $subject;
    protected $keywords;

    public function getName()
    {
        return $this->name;
    }

    public function getTitle()
    {
        $this->title;
    }
    public function getAuthor()
    {
        $this->author;
    }
    public function getCreator()
    {
        $this->creator;
    }
    public function getSubject()
    {
        $this->subject;
    }
    public function getKeywords()
    {
        $this->keywords;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }
    public function setAuthor($author)
    {
        $this->author = $author;
    }
    public function setCreator($creator)
    {
        $this->creator = $creator;
    }
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
    }
    function __construct()
    {
        $this->mpdf = new Mpdf();
    }
    public function generatePdf($name, $callBack)
    {

        $this->mpdf->WriteBarcode('0756291975');
        $this->mpdf->Output();
        $callBack();
    }
}
