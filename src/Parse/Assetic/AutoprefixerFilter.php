<?php namespace October\Rain\Parse\Assetic;

use Assetic\Filter\BaseNodeFilter;
use Assetic\Asset\AssetInterface;
use Assetic\Exception\FilterException;
use Assetic\Util\FilesystemUtils;

/**
 * Parses CSS and adds vendor prefixes to rules using values from the Can I Use website
 *
 * @link https://github.com/ai/autoprefixer
 * @author Alex Vasilenko <aa.vasilenko@gmail.com>
 */
class AutoprefixerFilter extends BaseNodeFilter
{
    /**
     * @var string
     */
    private $postcssBin;

    /**
     * @var array
     */
    private $browsers = array();

    public function __construct($postcssBin)
    {
        $this->postcssBin = $postcssBin;
    }

    /**
     * @param array $browsers
     */
    public function setBrowsers(array $browsers)
    {
        $this->browsers = $browsers;
    }

    /**
     * @param string $browser
     */
    public function addBrowser($browser)
    {
        $this->browsers[] = $browser;
    }

    public function filterLoad(AssetInterface $asset)
    {
        $input = $asset->getContent();
        $pb = $this->createProcessBuilder(array($this->postcssBin));

        $pb->setInput($input);

        $pb->add('--use')->add('autoprefixer');

        if ($this->browsers) {
            $pb->add('-browsers')->add(implode(',', $this->browsers));
        }

        $output = FilesystemUtils::createTemporaryFile('autoprefixer');
        $pb->add('-o')->add($output);

        $proc = $pb->getProcess();
        if (0 !== $proc->run()) {
            throw FilterException::fromProcess($proc)->setInput($asset->getContent());
        }

        $asset->setContent(file_get_contents($output));
        unlink($output);
    }

    /**
     * Filters an asset just before it's dumped.
     *
     * @param AssetInterface $asset An asset
     */
    public function filterDump(AssetInterface $asset)
    {
    }

}