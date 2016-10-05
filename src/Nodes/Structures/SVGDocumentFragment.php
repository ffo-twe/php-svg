<?php

namespace JangoBrick\SVG\Nodes\Structures;

use JangoBrick\SVG\Nodes\SVGNodeContainer;
use JangoBrick\SVG\SVGRenderingHelper;

class SVGDocumentFragment extends SVGNodeContainer
{
    private static $INITIAL_STYLES = array(
        'fill'          => '#000000',
        'stroke'        => 'none',
        'stroke-width'  => 1,
        'opacity'       => 1,
    );

    protected $x, $y, $width, $height;
    private $root;
    private $namespaces;

    public function __construct($root = false, $width = '100%', $height = '100%', $namespaces = array())
    {
        parent::__construct();

        $this->root = (bool) $root;
        $this->namespaces = $namespaces;

        $this->width  = $width;
        $this->height = $height;

        foreach (self::$INITIAL_STYLES as $style => $value) {
            $this->setStyle($style, $value);
        }
    }

    public function isRoot()
    {
        return $this->root;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }

    public function toXMLString()
    {
        $s  = '<svg';

        if ($this->root) {
            foreach ($this->namespaces as $namespace => $uri) {
                $s .= ' '. $namespace.'="'.$uri.'"';
            }
        } else {
            if ($this->x != 0) {
                $s .= ' x="'.$this->x.'"';
            }
            if ($this->y != 0) {
                $s .= ' y="'.$this->y.'"';
            }
        }

        if ($this->width != '100%') {
            $s .= ' width="'.$this->width.'"';
        }
        if ($this->height != '100%') {
            $s .= ' height="'.$this->height.'"';
        }

        if ($this->root) {
            $styles = array();
            // filter styles to not include initial/default ones
            foreach ($this->styles as $style => $value) {
                if (!isset(self::$INITIAL_STYLES[$style]) || self::$INITIAL_STYLES[$style] !== $value) {
                    $styles[$style] = $value;
                }
            }
        } else {
            $styles = $this->styles;
        }

        if (!empty($styles)) {
            $s .= ' style="';
            foreach ($styles as $style => $value) {
                $s .= $style.': '.$value.'; ';
            }
            $s .= '"';
        }

        $this->addAttributesToXMLString($s);

        $s .= '>';

        for ($i = 0, $n = $this->countChildren(); $i < $n; ++$i) {
            $child = $this->getChild($i);
            $s .= $child->toXMLString();
        }

        $s .= '</svg>';

        return $s;
    }

    public function draw(SVGRenderingHelper $rh, $scaleX, $scaleY)
    {
        if (!$this->root) {
            $rh->push();
            $rh->translate($this->x * $scaleX, $this->y * $scaleY);
        }

        for ($i = 0, $n = $this->countChildren(); $i < $n; ++$i) {
            $child = $this->getChild($i);
            $child->draw($rh, $scaleX, $scaleY);
        }

        if (!$this->root) {
            $rh->pop();
        }
    }
}
