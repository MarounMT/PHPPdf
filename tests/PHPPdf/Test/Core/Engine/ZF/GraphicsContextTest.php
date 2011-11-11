<?php

namespace PHPPdf\Test\Core\Engine\ZF;

use PHPPdf\Core\Engine\ZF\Engine;

use PHPPdf\Core\Engine\ZF\GraphicsContext;

class GraphicsContextTest extends \PHPPdf\PHPUnit\Framework\TestCase
{   
    /**
     * @test
     */
    public function clipRectangleWrapper()
    {
        $zendPageMock = $this->getMockBuilder('\Zend\Pdf\Page')
                             ->setMethods(array('clipRectangle'))
                             ->disableOriginalConstructor()
                             ->disableOriginalClone()
                             ->getMock();

        $x1 = 0;
        $x2 = 100;
        $y1 = 0;
        $y2 = 100;

        $zendPageMock->expects($this->once())
                     ->method('clipRectangle')
                     ->with($x1, $y1, $x2, $y2);

        $gc = new GraphicsContext($this->getEngineMock(), $zendPageMock);

        $gc->clipRectangle($x1, $y1, $x2, $y2);
        $gc->commit();
    }
    
    private function getEngineMock(array $methods = array())
    {
        $engine = $this->getMockBuilder('PHPPdf\Core\Engine\ZF\Engine')
                       ->setMethods($methods)
                       ->getMock();
        return $engine;
    }

    /**
     * @test
     */
    public function saveAndRestoreGSWrapper()
    {
        $zendPageMock = $this->getMock('\Zend\Pdf\Page', array('saveGS', 'restoreGS'), array(), '', false);

        $zendPageMock->expects($this->at(0))
                     ->method('saveGS');
        $zendPageMock->expects($this->at(1))
                     ->method('restoreGS');

        $gc = new GraphicsContext($this->getEngineMock(), $zendPageMock);

        $gc->saveGS();
        $gc->restoreGS();
        $gc->commit();
    }

    /**
     * @test
     */
    public function drawImageWrapper()
    {
        $x1 = 0;
        $x2 = 100;
        $y1 = 0;
        $y2 = 100;

        $zendPageMock = $this->getMockBuilder('\Zend\Pdf\Page')
                             ->setMethods(array('drawImage'))
                             ->disableOriginalConstructor()
                             ->getMock();
        $zendImage = $this->getMockBuilder('Zend\Pdf\Resource\Image\AbstractImage')
                          ->disableOriginalClone()
                          ->getMock();

        $image = $this->getMockBuilder('PHPPdf\Core\Engine\ZF\Image')
                      ->setMethods(array('getWrappedImage'))
                      ->disableOriginalConstructor()
                      ->getMock();
        $image->expects($this->once())
              ->method('getWrappedImage')
              ->will($this->returnvalue($zendImage));

        $zendPageMock->expects($this->once())
                     ->method('drawImage')
                     ->with($zendImage, $x1, $y1, $x2, $y2);

        $gc = new GraphicsContext($this->getEngineMock(), $zendPageMock);

        $gc->drawImage($image, $x1, $y1, $x2, $y2);
        $gc->commit();
    }

    /**
     * @test
     */
    public function drawLineWrapper()
    {
        $x1 = 0;
        $x2 = 100;
        $y1 = 0;
        $y2 = 100;

        $zendPageMock = $this->getMock('\Zend\Pdf\Page', array('drawLine'), array(), '', false);

        $zendPageMock->expects($this->once())
                     ->method('drawLine')
                     ->with($x1, $y1, $x2, $y2);

        $gc = new GraphicsContext($this->getEngineMock(), $zendPageMock);

        $gc->drawLine($x1, $y1, $x2, $y2);
        $gc->commit();
    }

    /**
     * @test
     */
    public function setFontWrapper()
    {
        $zendPageMock = $this->getMockBuilder('\Zend\Pdf\Page')
                             ->setMethods(array('setFont'))
                             ->disableOriginalConstructor()
                             ->getMock();
        $zendFontMock = $this->getMockBuilder('\Zend\Pdf\Resource\Font\AbstractFont')
                             ->disableOriginalClone()
                             ->getMock();

        $fontMock = $this->getMockBuilder('PHPPdf\Core\Engine\ZF\Font')
                         ->setMethods(array('getCurrentWrappedFont'))
                         ->disableOriginalConstructor()
                         ->getMock();

        $fontMock->expects($this->once())
                 ->method('getCurrentWrappedFont')
                 ->will($this->returnValue($zendFontMock));
        $size = 12;

        $zendPageMock->expects($this->once())
                     ->method('setFont')
                     ->with($zendFontMock, $size);

        $gc = new GraphicsContext($this->getEngineMock(), $zendPageMock);

        $gc->setFont($fontMock, $size);
        $gc->commit();
    }

    /**
     * @test
     * @dataProvider colorSetters
     */
    public function setColorsWrapper($method)
    {
        $zendPageMock = $this->getMock('\Zend\Pdf\Page', array($method), array(), '', false);
        $zendColor = $this->getMock('\Zend\Pdf\Color');
        $color = $this->getMockBuilder('PHPPdf\Core\Engine\ZF\Color')
                      ->setMethods(array('getWrappedColor', 'getComponents'))
                      ->disableOriginalConstructor()
                      ->getMock();
        $color->expects($this->once())
              ->method('getWrappedColor')
              ->will($this->returnValue($zendColor));

        $zendPageMock->expects($this->once())
                     ->method($method)
                     ->with($zendColor);

        $gc = new GraphicsContext($this->getEngineMock(), $zendPageMock);

        $gc->$method($color);

        //don't delegate if not necessary
        $gc->$method($color);
        $gc->commit();
    }

    public function colorSetters()
    {
        return array(
            array('setFillColor'),
            array('setLineColor'),
        );
    }

    /**
     * @test
     */
    public function drawPolygonWrapper()
    {
        $x = array(0, 100, 50);
        $y = array(0, 100, 50);
        $drawType = 1;

        $zendPageMock = $this->getMock('\Zend\Pdf\Page', array('drawPolygon'), array(), '', false);

        $zendPageMock->expects($this->once())
                     ->method('drawPolygon')
                     ->with($x, $y, $drawType);

        $gc = new GraphicsContext($this->getEngineMock(), $zendPageMock);

        $gc->drawPolygon($x, $y, $drawType);
        $gc->commit();
    }

    /**
     * @test
     */
    public function drawTextWrapper()
    {
        $x = 10;
        $y = 200;
        $text = 'some text';
        $encoding = 'utf-8';

        $zendPageMock = $this->getMock('\Zend\Pdf\Page', array('drawText'), array(), '', false);

        $zendPageMock->expects($this->once())
                     ->method('drawText')
                     ->with($text, $x, $y, $encoding);

        $gc = new GraphicsContext($this->getEngineMock(), $zendPageMock);

        $gc->drawText($text, $x, $y, $encoding);
        $gc->commit();
    }

    /**
     * @test
     */
    public function drawRoundedRectangleWrapper()
    {
        $x1 = 10;
        $y1 = 100;
        $x2 = 100;
        $y2 = 50;
        $radius = 0.5;
        $fillType = 1;

        $zendPageMock = $this->getMock('\Zend\Pdf\Page', array('drawRoundedRectangle'), array(), '', false);

        $zendPageMock->expects($this->once())
                     ->method('drawRoundedRectangle')
                     ->with($x1, $y1, $x2, $y2, $radius, $fillType);

        $gc = new GraphicsContext($this->getEngineMock(), $zendPageMock);

        $gc->drawRoundedRectangle($x1, $y1, $x2, $y2, $radius, $fillType);
        $gc->commit();
    }

    /**
     * @test
     */
    public function setLineWidthWrapper()
    {
        $width = 2.1;

        $zendPageMock = $this->getMock('\Zend\Pdf\Page', array('setLineWidth'), array(), '', false);

        $zendPageMock->expects($this->once())
                     ->method('setLineWidth')
                     ->with($width);

        $gc = new GraphicsContext($this->getEngineMock(), $zendPageMock);

        $gc->setLineWidth($width);

        //don't delegate if not necessary
        $gc->setLineWidth($width);
        $gc->commit();
    }

    /**
     * @test
     * @dataProvider lineDashingPatternProvider
     */
    public function setLineDashingPatternWrapper($pattern, $expected)
    {
        $zendPageMock = $this->getMock('\Zend\Pdf\Page', array('setLineDashingPattern'), array(), '', false);

        $zendPageMock->expects($this->once())
                     ->method('setLineDashingPattern')
                     ->with($expected);

        $gc = new GraphicsContext($this->getEngineMock(), $zendPageMock);

        $gc->setLineDashingPattern($pattern);

        //don't delegate if not necessary
        $gc->setLineDashingPattern($pattern);
        $gc->commit();
    }

    public function lineDashingPatternProvider()
    {
        return array(
            array(array(0), array(0)),
            array(GraphicsContext::DASHING_PATTERN_SOLID, 0),
            array(GraphicsContext::DASHING_PATTERN_DOTTED, array(1, 2))
        );
    }

    /**
     * @test
     */
    public function cachingGraphicsState()
    {
        $zendColor1 = $this->getMock('Zend\Pdf\Color', array('instructions', 'getComponents'));
        $zendColor2 = $this->getMock('Zend\Pdf\Color', array('instructions', 'getComponents'));

        $color1 = $this->createColorMock($zendColor1);
        $color2 = $this->createColorMock($zendColor2, array(9, 10, 11));

        $zendPageMock = $this->getMock('\Zend\Pdf\Page', array('setLineDashingPattern', 'setLineWidth', 'setFillColor', 'setLineColor', 'saveGS', 'restoreGS'), array(), '', false);

        $zendPageMock->expects($this->at(0))
                     ->method('saveGS');
        $zendPageMock->expects($this->at(1))
                     ->method('setLineDashingPattern');        
        $zendPageMock->expects($this->at(2))
                     ->method('setLineWidth');
        $zendPageMock->expects($this->at(3))
                     ->method('setFillColor');        
        $zendPageMock->expects($this->at(4))
                     ->method('setLineColor');
        $zendPageMock->expects($this->at(5))
                     ->method('restoreGS');
        $zendPageMock->expects($this->at(6))
                     ->method('setLineDashingPattern');        
        $zendPageMock->expects($this->at(7))
                     ->method('setLineWidth');
        $zendPageMock->expects($this->at(8))
                     ->method('setFillColor');        
        $zendPageMock->expects($this->at(9))
                     ->method('setLineColor');
        $zendPageMock->expects($this->at(10))
                     ->method('setLineDashingPattern');
        $zendPageMock->expects($this->at(11))
                     ->method('setLineWidth');
        $zendPageMock->expects($this->at(12))
                     ->method('setFillColor');
        $zendPageMock->expects($this->at(13))
                     ->method('setLineColor');


        $gc = new GraphicsContext($this->getEngineMock(), $zendPageMock);

        $gc->saveGS();
        //second loop pass do not change internal gc state
        for($i=0; $i<2; $i++)
        {
            $gc->setLineDashingPattern(array(1, 1));
            $gc->setLineWidth(1);
            $gc->setFillColor($color1);
            $gc->setLineColor($color1);
        }

        $gc->restoreGS();

        //second loop pass do not change internal gc state
        for($i=0; $i<2; $i++)
        {
            $gc->setLineDashingPattern(array(1, 1));
            $gc->setLineWidth(1);
            $gc->setFillColor($color1);
            $gc->setLineColor($color1);
        }

        //overriding by new values
        $gc->setLineDashingPattern(array(1, 2));
        $gc->setLineWidth(2);
        $gc->setFillColor($color2);
        $gc->setLineColor($color2);
        
        $gc->commit();
    }
    
    private function createColorMock($zendColor, array $components = null)
    {
        $color = $this->getMockBuilder('PHPPdf\Core\Engine\ZF\Color')
                      ->setMethods(array('getWrappedColor', 'getComponents'))
                      ->disableOriginalConstructor()
                      ->getMock();
                      
        $color->expects($this->any())
              ->method('getWrappedColor')
              ->will($this->returnValue($zendColor));
              
        if($components !== null)
        {
            $color->expects($this->any())
                  ->method('getComponents')
                  ->will($this->returnValue($components));
        }
              
        return $color;
    }
    
    /**
     * @test
     */
    public function attachUriAction()
    {
        $uri = 'http://google.com';
        $coords = array(0, 100, 200, 50);

        $zendPageMock = $this->getMockBuilder('\Zend\Pdf\Page')
                             ->setMethods(array('attachAnnotation'))
                             ->disableOriginalConstructor()
                             ->getMock();

        $zendPageMock->expects($this->once())
                     ->method('attachAnnotation')
                     ->with($this->validateByCallback(function($actual, \PHPUnit_Framework_TestCase $testCase) use($uri, $coords){
                         $testCase->assertAnnotationLinkWithRectangle($coords, $actual);
                         
                         $action = $actual->getDestination();
                         $testCase->assertInstanceOf('\Zend\Pdf\Action\Uri', $action);
                         $testCase->assertEquals($uri, $action->getUri());
                     }, $this));
                             
        $gc = new GraphicsContext($this->getEngineMock(), $zendPageMock);
        
        $gc->uriAction($coords[0], $coords[1], $coords[2], $coords[3], $uri);
        $gc->commit();
    }
    
    public function assertAnnotationLinkWithRectangle(array $coords, $actual)
    {
        $this->assertInstanceOf('\Zend\Pdf\Annotation\Link', $actual);
        
        $boundary = $actual->getResource()->Rect;
        
        foreach($coords as $i => $coord)
        {
            $this->assertEquals((string) $coord, $boundary->items[$i]->toString());
        }
    }
    
    /**
     * @test
     */
    public function attachGoToAction()
    {
        $zendPageMock = $this->getMockBuilder('\Zend\Pdf\Page')
                             ->setMethods(array('attachAnnotation'))
                             ->disableOriginalConstructor()
                             ->getMock();
                             
        $coords = array(0, 100, 200, 50);
        $top = 100;
        
        $pageStub = new \Zend\Pdf\Page('a4');
        $gcStub = new GraphicsContext($this->getEngineMock(), $pageStub);
        
        $zendPageMock->expects($this->once())
                     ->method('attachAnnotation')
                     ->with($this->validateByCallback(function($actual, \PHPUnit_Framework_TestCase $testCase) use($top, $coords, $pageStub){
                         $testCase->assertAnnotationLinkWithRectangle($coords, $actual);
                         
                         $destination = $actual->getDestination();
                         $testCase->assertZendPageDestination($top, $pageStub, $destination);

                     }, $this));
                     
        $gc = new GraphicsContext($this->getEngineMock(), $zendPageMock);
        
        $gc->goToAction($gcStub, $coords[0], $coords[1], $coords[2], $coords[3], $top);
        $gc->commit();
    }
    
    public function assertZendPageDestination($expectedTop, $expectedPage, $actualDestination)
    {
        $this->assertInstanceOf('\Zend\Pdf\Destination\FitHorizontally', $actualDestination);
        
        $this->assertEquals($expectedTop, $actualDestination->getTopEdge());
        $this->assertTrue($actualDestination->getResource()->items[0] === $expectedPage->getPageDictionary());
    }
    
    /**
     * @test
     * @expectedException PHPPdf\Exception\Exception
     * @dataProvider wrapZendExceptionsFromActionsProvider
     */
    public function wrapZendExceptionsFromActions($method, array $args)
    {
        $zendPageMock = $this->getMockBuilder('\Zend\Pdf\Page')
                             ->setMethods(array('attachAnnotation'))
                             ->disableOriginalConstructor()
                             ->getMock();
                             
        $zendPageMock->expects($this->any())
                     ->method('attachAnnotation')
                     ->will($this->throwException($this->getMock('\Zend\Pdf\Exception\RuntimeException')));

        $gc = new GraphicsContext($this->getEngineMock(), $zendPageMock);
        
        call_user_func_array(array($gc, $method), $args);
        $gc->commit();
    }
    
    public function wrapZendExceptionsFromActionsProvider()
    {
        return array(
            array(
                'goToAction', array(new GraphicsContext($this->getEngineMock(), new \Zend\Pdf\Page('a4')), 0, 0, 0, 0, 10),
            ),
            array(
                'uriAction', array(0, 0, 0, 0, 'invalid-uri'),
            ),
        );
    }
    
    /**
     * @test
     */
    public function attachSingleBookmark()
    {
        $pageStub = new \Zend\Pdf\Page('a4');
        $identifier = 'some id';
                             
        $top = 100;
        $bookmarkName = 'some name';
                            
        $engine = new Engine();
        $gc = new GraphicsContext($engine, $pageStub);
        
        $gc->addBookmark($identifier, $bookmarkName, $top);
        $gc->commit();
        
        $zendPdf = $engine->getZendPdf();
        
        $this->assertEquals(1, count($zendPdf->outlines));
        
        $outline = $zendPdf->outlines[0];
        
        $this->assertOutline($bookmarkName, $pageStub, $top, $outline);
    }
    
    private function assertOutline($expectedName, $expectedPage, $expectedTop, $actualOutline)
    {
        $this->assertEquals($expectedName, $actualOutline->getTitle());
        
        $target = $actualOutline->getTarget();
        
        $this->assertInstanceOf('\Zend\Pdf\Action\GoToAction', $target);
        $destination = $target->getDestination();
        
        $this->assertZendPageDestination($expectedTop, $expectedPage, $destination);
    }
    
    /**
     * @test
     */
    public function attachNestedBookmarks()
    {
        $pageStub = new \Zend\Pdf\Page('a4');
        
        $engine = new Engine();
        $gc = new GraphicsContext($engine, $pageStub);
        
        //child bookmark can be added before parent
        $gc->addBookmark(2, '2', 10, 1);
        $gc->addBookmark(1, '1', 0, null);
        $gc->addBookmark(3, '3', 0, null);
        $gc->commit();
        
        $zendPdf = $engine->getZendPdf();
        
        $this->assertEquals(2, count($zendPdf->outlines));
        
        $firstOutline = $zendPdf->outlines[0];
        $secondOutline = $zendPdf->outlines[1];
        
        $this->assertEquals(1, count($firstOutline->childOutlines));
        $this->assertEquals(0, count($secondOutline->childOutlines));
        
        $childOutline = $firstOutline->childOutlines[0];
        $this->assertOutline('2', $pageStub, 10, $childOutline);
    }
    
    /**
     * @test
     */
    public function attachStickyNote()
    {
        $zendPageMock = $this->getMockBuilder('\Zend\Pdf\Page')
                             ->setMethods(array('attachAnnotation'))
                             ->disableOriginalConstructor()
                             ->getMock();
        $gc = new GraphicsContext(new Engine(), $zendPageMock);
        
        $coords = array(1, 2, 3, 4);
        $text = 'text';
        
        $zendPageMock->expects($this->once())
                     ->method('attachAnnotation')
                     ->with($this->validateByCallback(function($actual, \PHPUnit_Framework_TestCase $testCase) use($text, $coords){
                         $testCase->assertInstanceOf('Zend\Pdf\Annotation\Text', $actual);
                         $rect = $actual->getResource()->Rect;

                         foreach($coords as $i => $coord)
                         {
                             $testCase->assertEquals($coord, $rect->items[$i]->toPhp());
                         }
                         $actualText = $actual->getResource()->Contents->toString();
                         $testCase->assertEquals($text, $actual->getResource()->Contents->toPhp());
                     }, $this));

        $gc->attachStickyNote($coords[0], $coords[1], $coords[2], $coords[3], $text);
        $gc->commit();
    }
    
    /**
     * @test
     * @dataProvider alphaProvider
     */
    public function setAlpha($alpha, $expectCall)
    {
        $zendPageMock = $this->getMockBuilder('\Zend\Pdf\Page')
                             ->setMethods(array('setAlpha'))
                             ->disableOriginalConstructor()
                             ->getMock();
                             
        $gc = new GraphicsContext(new Engine(), $zendPageMock);
        
        if($expectCall)
        {
            $zendPageMock->expects($this->at(0))
                         ->method('setAlpha')
                         ->with($alpha);
        }
        else
        {
            $zendPageMock->expects($this->never())
                         ->method('setAlpha');
        }

        $gc->setAlpha($alpha);
        $gc->setAlpha($alpha);
        $gc->commit();
    }
    
    public function alphaProvider()
    {
        return array(
            array(0.5, true),
            array(1, false),
        );
    }
    
    /**
     * @expectedException PHPPdf\Exception\Exception
     */
    public function throwExceptionIdParentOfBookmarkDosntExist()
    {
        $gc = new GraphicsContext(new Engine(), new \Zend\Pdf\Page('a4'));
        
        $gc->addBookmark('someId', 'some name', 100, 'unexistedParentId');
    }
}