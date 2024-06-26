<?php

class ConceptPropertyValueTest extends PHPUnit\Framework\TestCase
{
    private $model;
    private $concept;
    private $vocab;

    protected function setUp(): void
    {
        $this->model = new Model('/../../tests/testconfig.ttl');
        $this->vocab = $this->model->getVocabulary('test');
        $this->concept = $this->vocab->getConceptInfo('http://www.skosmos.skos/test/ta112', 'en');
    }

    /**
     * @covers ConceptPropertyValue::__construct
     */
    public function testConstructor()
    {
        $mockres = $this->getMockBuilder('EasyRdf\\Resource')->disableOriginalConstructor()->getMock();
        $mockres->method('getUri')->willReturn('http://example.org/');
        $propval = new ConceptPropertyValue($this->model, $this->vocab, $mockres, 'skosmos:testProp', 'en');
        $this->assertInstanceOf('ConceptPropertyValue', $propval);
    }

    /**
     * @covers ConceptPropertyValue::getLang
     */
    public function testGetLang()
    {
        $props = $this->concept->getProperties();
        $propvals = $props['skos:narrower']->getValues();
        $this->assertEquals('en', $propvals['Crucian carp http://www.skosmos.skos/test/ta121']->getLang());
    }

    /**
     * @covers ConceptPropertyValue::getSortKey
     */
    public function testGetSortKey()
    {
        $props = $this->concept->getProperties();
        $propvals = $props['skos:narrower']->getValues();
        $this->assertEquals('crucian carp', $propvals['Crucian carp http://www.skosmos.skos/test/ta121']->getSortKey());
    }

    /**
     * @covers ConceptPropertyValue::getLabel
     */
    public function testGetLabel()
    {
        $props = $this->concept->getProperties();
        $propvals = $props['skos:narrower']->getValues();
        $this->assertEquals('Crucian carp', $propvals['Crucian carp http://www.skosmos.skos/test/ta121']->getLabel());
    }

    /**
     * @covers ConceptPropertyValue::getLabel
     */
    public function testGetLabelLiteral()
    {
        $mockres = $this->getMockBuilder('EasyRdf\\Resource')->disableOriginalConstructor()->getMock();
        $labelmap = array(
          array('en', null),
          array(null, null)
        );
        $mockres->method('label')->will($this->returnValueMap($labelmap));
        $litmap = array(
          array('rdf:value', 'en', 'english lit'),
          array('rdf:value', null, 'default lit')
        );
        $mockres->method('getLiteral')->will($this->returnValueMap($litmap));
        $mockres->method('getUri')->will($this->returnValue('http://thisdoesntexistatalland.sefsf/2j2h4/'));
        $mapping = new ConceptPropertyValue($this->model, $this->vocab, $mockres, null);
        $this->assertEquals('english lit', $mapping->getLabel('en'));
        $this->assertEquals('default lit', $mapping->getLabel());
    }

    /**
     * @covers ConceptPropertyValue::getType
     */
    public function testGetType()
    {
        $props = $this->concept->getProperties();
        $propvals = $props['skos:narrower']->getValues();
        $this->assertEquals('skos:narrower', $propvals['Crucian carp http://www.skosmos.skos/test/ta121']->getType());
    }

    /**
     * @covers ConceptPropertyValue::getUri
     */
    public function testGetUri()
    {
        $props = $this->concept->getProperties();
        $propvals = $props['skos:narrower']->getValues();
        $this->assertEquals('http://www.skosmos.skos/test/ta121', $propvals['Crucian carp http://www.skosmos.skos/test/ta121']->getUri());
    }

    /**
     * @covers ConceptPropertyValue::getVocab
     */
    public function testGetVocab()
    {
        $props = $this->concept->getProperties();
        $vocab = $this->model->getVocabulary('test');
        $propvals = $props['skos:narrower']->getValues();
        $this->assertEquals($vocab, $propvals['Crucian carp http://www.skosmos.skos/test/ta121']->getVocab());
    }

    /**
     * @covers ConceptPropertyValue::getVocabName
     */
    public function testGetVocabName()
    {
        $props = $this->concept->getProperties();
        $propvals = $props['skos:narrower']->getValues();
        $this->assertEquals('Test short', $propvals['Crucian carp http://www.skosmos.skos/test/ta121']->getVocabName());
    }

    /**
     * @covers ConceptPropertyValue::getNotation
     */
    public function testGetNotation()
    {
        $concept = $this->vocab->getConceptInfo('http://www.skosmos.skos/test/ta121', 'en');
        $props = $concept->getProperties();
        $propvals = $props['skos:broader']->getValues();
        $this->assertEquals(665, $propvals['665 Carp http://www.skosmos.skos/test/ta112']->getNotation());
    }

    /**
     * @covers ConceptPropertyValue::getNotation
     */
    public function testGetNotationWhenThereIsNone()
    {
        $mockres = $this->getMockBuilder('EasyRdf\\Resource')->disableOriginalConstructor()->getMock();
        $mockres->method('getUri')->willReturn('http://example.org/');
        $propval = new ConceptPropertyValue($this->model, $this->vocab, $mockres, 'en');
        $this->assertEquals(null, $propval->getNotation());
    }

    /**
     * @covers ConceptPropertyValue::__toString
     */
    public function testToStringWhenSortByNotationNotSet()
    {
        $concept = $this->vocab->getConceptInfo('http://www.skosmos.skos/test/ta121', 'en');
        $props = $concept->getProperties();
        $propvals = $props['skos:broader']->getValues();
        $this->assertEquals('Carp', (string)$propvals['665 Carp http://www.skosmos.skos/test/ta112']);
    }

    /**
     * @covers ConceptPropertyValue::addSubMember
     * @covers ConceptPropertyValue::sortSubMembers
     * @covers ConceptPropertyValue::getSubMembers
     */
    public function testSubmemberSorting()
    {
        $concept = $this->vocab->getConceptInfo('http://www.skosmos.skos/test/ta121', 'en');
        $props = $concept->getProperties();
        $propvals = $props['skos:broader']->getValues();
        $prop = reset($propvals);
        $val1 = $this->getMockBuilder('ConceptPropertyValue')->disableOriginalConstructor()->getMock();
        $lit1 = $this->getMockBuilder('EasyRdf\\Literal')->disableOriginalConstructor()->getMock();
        $lit1->method('getValue')->will($this->returnValue('elephant'));
        $val1->method('getLabel')->will($this->returnValue($lit1));
        $val2 = $this->getMockBuilder('ConceptPropertyValue')->disableOriginalConstructor()->getMock();
        $lit2 = $this->getMockBuilder('EasyRdf\\Literal')->disableOriginalConstructor()->getMock();
        $lit2->method('getValue')->will($this->returnValue('cat'));
        $val2->method('getLabel')->will($this->returnValue($lit2));
        $val3 = $this->getMockBuilder('ConceptPropertyValue')->disableOriginalConstructor()->getMock();
        $lit3 = $this->getMockBuilder('EasyRdf\\Literal')->disableOriginalConstructor()->getMock();
        $lit3->method('getValue')->will($this->returnValue('cheetah'));
        $val3->method('getLabel')->will($this->returnValue($lit3));
        $prop->addSubMember($val1);
        $prop->addSubMember($val2);
        $prop->addSubMember($val3);
        $this->assertEquals(array('cat', 'cheetah', 'elephant'), array_keys($prop->getSubMembers()));
    }

    /**
     * @covers ConceptPropertyValue::getSubMembers
     */
    public function testGetSubMembersEmpty()
    {
        $mockres = $this->getMockBuilder('EasyRdf\\Resource')->disableOriginalConstructor()->getMock();
        $mockres->method('getUri')->willReturn('http://example.org/');
        $propval = new ConceptPropertyValue($this->model, $this->vocab, $mockres, 'en');
        $this->assertEquals(null, $propval->getSubMembers());
    }

    public function testGetReifiedPropertyValues()
    {
        $vocab = $this->model->getVocabulary('xl');
        $concept = $vocab->getConceptInfo('http://www.skosmos.skos/xl/c1', 'en');
        $props = $concept->getProperties();
        $vals = $props['skos:definition']->getValues();
        $val = reset($vals);
        $reified_vals = $val->getReifiedPropertyValues();
        $this->assertCount(2, $reified_vals);
        $this->assertArrayHasKey('Source', $reified_vals);
        $this->assertEquals('https://en.wikipedia.org/wiki/Concept', $reified_vals['Source']->getLabel());
        $this->assertArrayHasKey('Last modified', $reified_vals);
        $this->assertEquals('4/13/18', $reified_vals['Last modified']->getLabel());
    }
}
