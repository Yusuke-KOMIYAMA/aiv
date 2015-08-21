<?php
/**
 * AnnotationUtil.class.php
 * Date: 2015/03/30
 */
class AnnotationUtil {

    const FIGURE_TYPE_ELLIPSE  = 1;
    const FIGURE_TYPE_RECT     = 2;
    const FIGURE_TYPE_ARROW    = 3;
    const FIGURE_TYPE_LINE     = 4;
    const FIGURE_TYPE_ANNOTATE = 5;

    const COMMENT_ICON = 'M3.161,63.357c0.471,0,0.968-0.115,1.479-0.342l14.346-6.376c1.234-0.549,2.887-1.684,3.843-2.64L62,14.829 c0.754-0.754,1.17-1.759,1.17-2.829S62.754,9.925,62,9.172l-7.172-7.173C54.074,1.246,53.07,0.831,52,0.831S49.926,1.246,49.172,2 L9,42.171c-0.968,0.967-2.09,2.651-2.612,3.917L0.912,59.389c-0.594,1.444-0.174,2.42,0.129,2.873 C1.507,62.958,2.28,63.357,3.161,63.357z M20,51.171C20,51.171,20,51.172,20,51.171L12.828,44L46,10.828L53.172,18L20,51.171z M52,4.828L59.172,12L56,15.172L48.828,8L52,4.828z M10.088,47.611c0.059-0.142,0.138-0.303,0.226-0.469l6.213,6.213L5.751,58.143 L10.088,47.611z';

    // アノテーションつき画像
    private $dst = null;

    // Image設定
    private $src = null;
    private $ext = null;
    private $width = 0;
    private $height = 0;
    private $type = null;

    // SVG(Annotation)設定
    private $version = null;
    private $encoding = null;
    private $dom = null;
    private $svg = null;
    private $defs = null;
    public $svgFile = null;

    // 作業ディレクトリ設定
    private $tmp = null;

    /**
     * コンストラクタ
     *
     * @param $image
     * @param $dst
     * @param $tmp
     * @param string $version
     * @param string $encoding
     * @throws Exception
     */
    public function __construct($image, $dst, $tmp, $version = '1.0', $encoding = 'UTF-8')
    {
        try {
            // 画像の設定
            $this->src = $image;
            list($this->width, $this->height, $this->type) = $this->_getImageInfo($image);
            $this->ext = $this->_getExt($this->type);
            $info = pathinfo($image);
            $this->dst = $dst."/".$info["filename"]."_ANNOTATION.".$this->ext;

            // SVG(アノテーション)の設定
            $this->version = $version;
            $this->encoding = null;

            // 作業ディレクトリ設定
            $this->tmp = $tmp;
Debugger::log($version, LOG_DEBUG);
            // 作業SVGファイルの設定
            $this->svgFile = $this->tmp . "/" . $info["filename"] . ".svg";
        }
        catch(Exception $e) {
            throw $e;
        }

    }

    /**
     * 画像へアノテーションを描画する
     *
     * @param $annotations
     * @return null|string
     */
    public function composite($annotations)
    {
        // メモリ上でSVGを生成し、アノテーションをセットする
        $this->createSvg($this->width, $this->height);
        $this->setDefs();
        $this->setAnnotation($annotations);

        // SVGを保存する
        $this->saveAnnotation($this->svgFile);

        // Image に SVGを合成する
        set_time_limit(600);
        exec('convert '.$this->src.' -background none '.$this->svgFile.' -composite '.$this->dst);
        return $this->dst;
    }

    /**
     * SVGを生成する
     *
     * @param $width
     * @param $height
     */
    public function createSvg($width, $height)
    {
        $this->dom = new DomDocument($this->version, $this->encoding);
        $this->svg = $this->dom->appendChild($this->dom->createElement('svg'));
        $this->svg->setAttribute('width', $width);
        $this->svg->setAttribute('height', $height);
    }

    /**
     * アノテーション用defsを設定する
     */
    public function setDefs()
    {
        // ここは、このアプリケーション固有の情報ですね。別にしたい。

        // <defs>
        //     <marker viewBox="0 0 10 10" markerWidth="10" markerHeight="10" orient="auto" refX="5" refY="5" id="marker">
        //         <path d="M0,0L8,5L0,10L4,5z" stroke="#ff0000" fill="#ff0000"></path>
        //     </marker>
        // </defs>
        $defs = $this->svg->appendChild($this->dom->createElement('defs'));
        $marker = $defs->appendChild($this->dom->createElement('marker'));
        $marker->setAttribute('id', 'marker');
        $marker->setAttribute('viewBox', '0 0 10 10');
        $marker->setAttribute('markerWidth', '10');
        $marker->setAttribute('markerHeight', '10');
        $marker->setAttribute('orient', 'auto');
        $marker->setAttribute('refX', '5');
        $marker->setAttribute('refY', '5');
        $path = $marker->appendChild($this->dom->createElement('path'));
        $path->setAttribute('d', 'M0,0L8,5L0,10L4,5z');
        $path->setAttribute('stroke', '#ff0000');
        $path->setAttribute('fill', '#ff0000');

        $this->defs = $defs;
    }

    /**
     * アノテーションをセットする
     *
     * @param $annotations
     */
    public function setAnnotation($annotations)
    {
        // 図形を描画する
        foreach($annotations as $annotation)
        {
            $this->addFigure(
                $annotation["figureType"],
                $annotation["svgId"],
                $annotation["x"],
                $annotation["y"],
                $annotation["x2"],
                $annotation["y2"],
                $annotation["rx"],
                $annotation["ry"],
                $annotation["width"],
                $annotation["height"],
                $annotation["stroke"],
                $annotation["strokeWidth"],
                $annotation["lineStyle"],
                'none'
            );
        }

    }

    /**
     * 図形を描画する
     *
     * @param $type
     * @param $x
     * @param $y
     * @param $x2
     * @param $y2
     * @param $rx
     * @param $ry
     * @param $width
     * @param $height
     * @param $stroke
     * @param $strokeWidth
     * @param $lineStyle
     * @param string $fill
     */
    public function addFigure($type, $id, $x, $y, $x2, $y2, $rx, $ry, $width, $height, $stroke, $strokeWidth, $lineStyle, $fill = 'none')
    {
        switch ($type) {

            case AnnotationUtil::FIGURE_TYPE_ELLIPSE:

                // <ellipse cx="100" cy="100" rx="50" ry="75" stroke="red" fill="blue" stroke-width="5" />
                $figure = $this->svg->appendChild($this->dom->createElement('ellipse'));
                $figure->setAttribute('cx', $x);
                $figure->setAttribute('cy', $y);
                $figure->setAttribute('rx', $rx);
                $figure->setAttribute('ry', $ry);
                $figure->setAttribute('stroke', $stroke);
                $figure->setAttribute('stroke-width', $strokeWidth);
                $figure->setAttribute('fill', $fill);
                break;

            case AnnotationUtil::FIGURE_TYPE_RECT:

                // <rect x="320" y="170" width="120" height="100" rx="20" ry="20" fill="white" stroke="red" stroke-width="5" />
                $figure = $this->svg->appendChild($this->dom->createElement('rect'));
                $figure->setAttribute('x', $x);
                $figure->setAttribute('y', $y);
                $figure->setAttribute('rx', $rx);
                $figure->setAttribute('ry', $ry);
                $figure->setAttribute('width', $width);
                $figure->setAttribute('height', $height);
                $figure->setAttribute('stroke', $stroke);
                $figure->setAttribute('stroke-width', $strokeWidth);
                $figure->setAttribute('fill', $fill);
                break;

            case AnnotationUtil::FIGURE_TYPE_ARROW:

                $marker = $this->defs->appendChild($this->dom->createElement('marker'));
                $marker->setAttribute('id', 'm_' . $id);
                $marker->setAttribute('viewBox', '0 0 10 10');
                $marker->setAttribute('markerWidth', '10');
                $marker->setAttribute('markerHeight', '10');
                $marker->setAttribute('orient', 'auto');
                $marker->setAttribute('refX', '5');
                $marker->setAttribute('refY', '5');
                $path = $marker->appendChild($this->dom->createElement('path'));
                $path->setAttribute('d', 'M0,0L8,5L0,10L4,5z');
                $path->setAttribute('stroke', $stroke);
                $path->setAttribute('fill', $stroke);

                // <line x1="100" x2="150" y1="200" y2="150" stroke="#ff0000" markerWidth="1" markerHeight="1" stroke-width="6" marker-end="url(#MARKER)" />
                $figure = $this->svg->appendChild($this->dom->createElement('line'));
                $figure->setAttribute('x1', $x);
                $figure->setAttribute('y1', $y);
                $figure->setAttribute('x2', $x2);
                $figure->setAttribute('y2', $y2);
                $figure->setAttribute('stroke', $stroke);
                $figure->setAttribute('stroke-width', $strokeWidth);
                $figure->setAttribute('fill', $fill);
                $figure->setAttribute('markerWidth', 1);
                $figure->setAttribute('markerHeight', 1);
                $figure->setAttribute('marker-end', 'url(#m_'.$id.')');
                break;

            case AnnotationUtil::FIGURE_TYPE_LINE:

                // <line x1="100" x2="150" y1="200" y2="150" stroke="#ff0000" stroke-width="6" />
                $figure = $this->svg->appendChild($this->dom->createElement('line'));
                $figure->setAttribute('x1', $x);
                $figure->setAttribute('y1', $y);
                $figure->setAttribute('x2', $x2);
                $figure->setAttribute('y2', $y2);
                $figure->setAttribute('stroke', $stroke);
                $figure->setAttribute('stroke-width', $strokeWidth);
                $figure->setAttribute('fill', $fill);
                break;

            case AnnotationUtil::FIGURE_TYPE_ANNOTATE:

                // <g>
                //     <g>
                //         <path d="M3.161,63.357c0.471,0,0.968-0.115,1.479-0.342l14.346-6.376c1.234-0.549,2.887-1.684,3.843-2.64L62,14.829 c0.754-0.754,1.17-1.759,1.17-2.829S62.754,9.925,62,9.172l-7.172-7.173C54.074,1.246,53.07,0.831,52,0.831S49.926,1.246,49.172,2 L9,42.171c-0.968,0.967-2.09,2.651-2.612,3.917L0.912,59.389c-0.594,1.444-0.174,2.42,0.129,2.873 C1.507,62.958,2.28,63.357,3.161,63.357z M20,51.171C20,51.171,20,51.172,20,51.171L12.828,44L46,10.828L53.172,18L20,51.171z M52,4.828L59.172,12L56,15.172L48.828,8L52,4.828z M10.088,47.611c0.059-0.142,0.138-0.303,0.226-0.469l6.213,6.213L5.751,58.143 L10.088,47.611z"></path>
                //     </g>
                // </g>
                $g1 = $this->svg->appendChild($this->dom->createElement('g'));
                $g1->setAttribute('transform', 'translate('.$x.','.($y-64).')');
                $g2 = $g1->appendChild($this->dom->createElement('g'));
                $path = $g2->appendChild($this->dom->createElement('path'));
                $path->setAttribute('d', AnnotationUtil::COMMENT_ICON);
                break;

            default:
                break;

        }
    }

    /**
     * SVGを保存する
     *
     * @param $file
     */
    public function saveAnnotation($file)
    {
        // SVG出力
        $this->dom->save($file);
    }

    /**
     * 画像の情報を取得する
     *
     * @param $name
     * @return array|bool
     */
    private function _getImageInfo($name)
    {
        if (!file_exists($name)) {
            throw new NotFoundException("[AnnotationUtilError] Image file does not found.");
        }
        return getimagesize($name);
    }

    /**
     * 拡張子の取得
     *
     * @param $imagetype
     * @return bool|string
     */
    private function _getExt($imagetype)
    {
        switch($imagetype) {
            case IMAGETYPE_GIF:
                return "gif";
            case IMAGETYPE_JPEG:
                return "jpg";
            case IMAGETYPE_PNG:
                return "png";
            default:
                throw new CakeException("[AnnotationUtilError] Image file type is not supported.");
        }
    }

}
