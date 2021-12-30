<?php

declare(strict_types=1);

namespace PHPPdf\ObjectMother;

use PHPUnit\Framework\TestCase;
use PHPPdf\Core\Boundary;
use PHPPdf\Core\Node\Table\Cell;

class TableObjectMother
{
    private $test;

    public function __construct(TestCase $test)
    {
        $this->test = $test;
    }

    public function getCellMockWithTranslateAndResizeExpectations($width, $newWidth, $translateX)
    {
        $boundary = $this->test->getMockBuilder(Boundary::class)
                               ->onlyMethods(['pointTranslate'])
                               ->addMethods(['getMinWidth'])
                               ->getMock();

        $cell = $this->getCellMockWithResizeExpectations($width, $newWidth);

        if ($translateX !== false) {
            $cell->expects($this->test->once())
                 ->method('translate')
                 ->with($translateX, 0);
        }

        return $cell;
    }

    public function getCellMockWithResizeExpectations($width, $newWidth, $invokeResizeMethod = true)
    {
        $cell = $this->test->getMockBuilder(Cell::class)->onlyMethods(['getWidth', 'getBoundary', 'setWidth', 'translate', 'getNumberOfColumn', 'resize'])
                           ->getMock();

        $cell->expects($this->test->any())
             ->method('getWidth')
             ->willReturn($width);
        $cell->expects($this->test->once())
             ->method('setWidth')
             ->with($newWidth);

        if ($invokeResizeMethod) {
            $cell->expects($this->test->once())
                 ->method('resize')
                 ->with($newWidth - $width);
        } else {
            $cell->expects($this->test->never())
                 ->method('resize');
        }

        return $cell;
    }
}
