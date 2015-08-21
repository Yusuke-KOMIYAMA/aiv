<?php
/**
 * Image.php
 *
 * �摜���f��
 */
class Image extends AppModel {

    public $name = 'Image';
    public $useTable = 'images';
    public $primaryKey = 'id';

    public $recursive = -1;

    /**
     * @param $fileName
     * @param $localFileName
     * @param $deepZoomImage
     * @param $fileSize
     * @param $x
     * @param $y
     * @return mixed
     * @throws Exception
     */
    public function saveFromPenServer($fileName, $localFileName, $deepZoomImage, $fileSize, $x, $y)
    {
        $data = array(
            'Image' => array(
                'fileName' => $fileName,
                'localFileName' => $localFileName,
                'deepZoomImage' => $deepZoomImage,
                'fileSize' => $fileSize,
                'sizeX' => $x,
                'sizeY' => $y,
            ),
        );
        $this->create(false);
        $this->save($data);
        $imageId = $this->getLastInsertID();

        return $imageId;
    }
}

