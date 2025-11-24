<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202511\Symfony\Component\Console\Formatter;

use RectorPrefix202511\Symfony\Component\Console\Exception\InvalidArgumentException;
use RectorPrefix202511\Symfony\Contracts\Service\ResetInterface;
/**
 * @author Jean-François Simon <contact@jfsimon.fr>
 */
class OutputFormatterStyleStack implements ResetInterface
{
    /**
     * @var OutputFormatterStyleInterface[]
     */
    private $styles = [];
    /**
     * @var \RectorPrefix202511\Symfony\Component\Console\Formatter\OutputFormatterStyleInterface
     */
    private $emptyStyle;
    public function __construct(?\RectorPrefix202511\Symfony\Component\Console\Formatter\OutputFormatterStyleInterface $emptyStyle = null)
    {
        $this->emptyStyle = $emptyStyle ?? new \RectorPrefix202511\Symfony\Component\Console\Formatter\OutputFormatterStyle();
        $this->reset();
    }
    /**
     * Resets stack (ie. empty internal arrays).
     *
     * @return void
     */
    public function reset()
    {
        $this->styles = [];
    }
    /**
     * Pushes a style in the stack.
     *
     * @return void
     */
    public function push(\RectorPrefix202511\Symfony\Component\Console\Formatter\OutputFormatterStyleInterface $style)
    {
        $this->styles[] = $style;
    }
    /**
     * Pops a style from the stack.
     *
     * @throws InvalidArgumentException When style tags incorrectly nested
     */
    public function pop(?\RectorPrefix202511\Symfony\Component\Console\Formatter\OutputFormatterStyleInterface $style = null): \RectorPrefix202511\Symfony\Component\Console\Formatter\OutputFormatterStyleInterface
    {
        if (!$this->styles) {
            return $this->emptyStyle;
        }
        if (null === $style) {
            return array_pop($this->styles);
        }
        foreach (array_reverse($this->styles, \true) as $index => $stackedStyle) {
            if ($style->apply('') === $stackedStyle->apply('')) {
                $this->styles = \array_slice($this->styles, 0, $index);
                return $stackedStyle;
            }
        }
        throw new InvalidArgumentException('Incorrectly nested style tag found.');
    }
    /**
     * Computes current style with stacks top codes.
     */
    public function getCurrent(): \RectorPrefix202511\Symfony\Component\Console\Formatter\OutputFormatterStyleInterface
    {
        if (!$this->styles) {
            return $this->emptyStyle;
        }
        return $this->styles[\count($this->styles) - 1];
    }
    /**
     * @return $this
     */
    public function setEmptyStyle(\RectorPrefix202511\Symfony\Component\Console\Formatter\OutputFormatterStyleInterface $emptyStyle)
    {
        $this->emptyStyle = $emptyStyle;
        return $this;
    }
    public function getEmptyStyle(): \RectorPrefix202511\Symfony\Component\Console\Formatter\OutputFormatterStyleInterface
    {
        return $this->emptyStyle;
    }
}
