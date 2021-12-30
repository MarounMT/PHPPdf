<?php

namespace PHPPdf\Test\Core;

use PHPPdf\Core\FacadeBuilder,
    PHPPdf\Cache\CacheImpl,
    PHPPdf\Core\FacadeConfiguration;

class FacadeBuilderTest extends \PHPPdf\PHPUnit\Framework\TestCase
{
    private $builder;
    private $configurationLoader;

    public function setUp(): void
    {
        $this->configurationLoader = $this->getMockBuilder('PHPPdf\Core\Configuration\Loader')
                                          ->getMock();
        $this->builder = FacadeBuilder::create($this->configurationLoader);
        
        $complexAttributeFactory = $this->createMock('PHPPdf\Core\ComplexAttribute\ComplexAttributeFactory');
        
        $this->configurationLoader->expects($this->any())
                                  ->method('createComplexAttributeFactory')
                                  ->willReturn($complexAttributeFactory);
    }

    /**
     * @test
     */
    public function returnFacadeOnBuildMethod()
    {        
        $facade = $this->builder->build();
        $this->assertInstanceOf('PHPPdf\Core\Facade', $facade);
        $configurationLoader = $this->getAttribute($facade, 'configurationLoader');
        $this->assertTrue($configurationLoader === $this->configurationLoader);
    }

    /**
     * @test
     */
    public function cacheIsOffByDefault()
    {
        $facade = $this->builder->build();

        $this->assertInstanceOf('PHPPdf\Cache\NullCache', $this->getAttribute($facade, 'cache'));
    }

    /**
     * @test
     */
    public function settingCacheConfiguration()
    {
        $this->configurationLoader->expects($this->once())
                                  ->method('setCache');
        $facade = $this->builder->setCache(CacheImpl::ENGINE_FILE, array())->build();

        $this->assertInstanceOf('PHPPdf\Cache\CacheImpl', $this->getAttribute($facade, 'cache'));
    }

    /**
     * @test
     * @dataProvider booleanProvider
     */
    public function switchingOnAndOffStylesheetConstraintCache($useCache)
    {
        $facade = $this->builder->setUseCacheForStylesheetConstraint($useCache)->build();

        $this->assertEquals($useCache, $this->getAttribute($facade, 'useCacheForStylesheetConstraint'));
    }

    public function booleanProvider()
    {
        return array(
            array(false),
            array(true),
        );
    }
    
    /**
     * @test
     * @dataProvider booleanProvider
     */
    public function switchOnAndOffCacheForConfigurationLoader($useCache)
    {
        $this->builder->setUseCacheForConfigurationLoader($useCache);
        $this->builder->setCache('File', array('cache_dir' => TEST_RESOURCES_DIR));
                       
        $this->configurationLoader->expects($useCache ? $this->once() : $this->never())
                                  ->method('setCache');
               
        $this->builder->build();
    }
    
    /**
     * @test
     * @dataProvider injectProperDocumentParserToFacadeProvider
     */
    public function injectProperDocumentParserToFacade($type, $expectedClass)
    {
        $this->builder->setDocumentParserType($type);
        
        $facade = $this->builder->build();
        
        $this->assertInstanceof($expectedClass, $facade->getDocumentParser());
    }
    
    public function injectProperDocumentParserToFacadeProvider()
    {
        return array(
            array(
                FacadeBuilder::PARSER_XML,
                'PHPPdf\Core\Parser\XmlDocumentParser',
            ),
            array(
                FacadeBuilder::PARSER_MARKDOWN,
                'PHPPdf\Core\Parser\MarkdownDocumentParser',
            ),
        );
    }
    
    /**
     * @test
     * @dataProvider createParameterizeDocumentUsingEngineFromEngineFactoryProvider
     */
    public function createParameterizeDocumentUsingEngineFromEngineFactory($type, $options)
    {
        $engineFactory = $this->createMock('PHPPdf\Core\Engine\EngineFactory');
        
        $builder = FacadeBuilder::create(null, $engineFactory);
        
        $builder->setEngineType($type)
                ->setEngineOptions($options);

        $engine = $this->createMock('PHPPdf\Core\Engine\Engine');
                
        $engineFactory->expects($this->once())
                      ->method('createEngine')
                      ->with($type, $options)
                      ->willReturn($engine);
                      
        $facade = $builder->build();
        
        $this->assertInstanceOf(get_class($engine), $facade->getDocument()->getEngine());
    }
    
    public function createParameterizeDocumentUsingEngineFromEngineFactoryProvider()
    {
        return array(
            array('type', array('some-options')),
        );
    }
    
    /**
     * @test
     */
    public function addStringFilter()
    {
        $filter = $this->createMock('PHPPdf\Util\StringFilter');
        
        $this->builder->addStringFilter($filter);
        
        $facade = $this->builder->build();
        
        $document = $facade->getDocument();
        
        $builderStringFilters = $this->getAttribute($this->builder, 'stringFilters');
        $documentStringFilters = $this->getAttribute($document, 'stringFilters');
        $facadeStringFilters = $this->getAttribute($facade, 'stringFilters');
        
        $this->assertEquals(2, count($builderStringFilters));
        $this->assertEquals($builderStringFilters, $documentStringFilters);
        $this->assertEquals($builderStringFilters, $facadeStringFilters);
    }
}
