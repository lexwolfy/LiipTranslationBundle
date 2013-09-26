<?php

namespace Liip\TranslationBundle\Tests;

use Liip\TranslationBundle\Model\Translation;
use Liip\TranslationBundle\Model\Unit;
use Liip\TranslationBundle\Repository\UnitRepository;

/**
 * To be completed
 *
 * This file is part of the LiipTranslationBundle test suite.
 * For more information concerning the bundle, see the README.md file at the project root.
 *
 * @package Liip\TranslationBundle\Tests\Translation\Loader
 * @version 0.0.1
 *
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Jeanmonod <david.jeanmonod@liip.ch>
 * @author Gilles Meier <gilles.meier@liip.ch>
 * @copyright Copyright (c) 2013, Liip, http://www.liip.ch
 */
class RepositoryTest extends \PHPUnit_Framework_TestCase
{
    protected function getLocaleList()
    {
        return array('fr', 'en');
    }

    protected function getRepository(array $units = null)
    {
        $config = array(
            'locale_list' => $this->getLocaleList()
        );

        $translator = $this->getMockBuilder('Liip\TranslationBundle\Translation\Translator');
        $translator->disableOriginalConstructor();
        $translator = $translator->getMock();

        $persistence = $this->getMock('Liip\TranslationBundle\Persistence\PersistenceInterface');
        if(! is_null($units)) {
            $persistence->expects($this->once())->method('getUnits')->will($this->returnValue($units));
        }
        return new UnitRepository($config, $translator, $persistence);
    }

    public function testLocaleList()
    {
        $this->assertEquals($this->getLocaleList(), $this->getRepository()->getLocaleList());
    }

    public function testFindAll()
    {
        $this->assertEmpty($this->getRepository()->findAll());
        $this->assertCount(5, $this->getRepository(array(1, 2, 3, 4, 5))->findAll());
        // test successive loads
        $data = array('toto', 'tata', 'titi');
        $repo = $this->getRepository($data);
        $this->assertEquals($data, $repo->findAll());
        $this->assertEquals($data, $repo->findAll());
        $this->assertEquals($data, $repo->findAll());
    }

    public function testFindBy()
    {
        $u1_1 = new Unit('domain1', 'key1');
        $u1_2 = new Unit('domain1', 'key2');
        $u1_3 = new Unit('domain1', 'key3');

        $u2_1 = new Unit('domain2', 'key1');
        $u2_2 = new Unit('domain2', 'key2');

        $u3_1 = new Unit('domain3', 'key1');

        $repo = $this->getRepository(array($u1_1, $u1_2, $u1_3, $u2_1, $u2_2, $u3_1));

        $this->assertEquals(array($u1_1, $u1_2, $u1_3), $repo->findByDomain('domain1'));
        $this->assertEquals(array($u2_1, $u2_2), $repo->findByDomain('domain2'));
        $this->assertEquals(array($u3_1), $repo->findByDomain('domain3'));

        $this->assertEquals(array($u1_1, $u2_1, $u3_1), $repo->findByTranslationKey('key1'));
        $this->assertEquals(array($u1_2, $u2_2), $repo->findByTranslationKey('key2'));
        $this->assertEquals(array($u1_3), $repo->findByTranslationKey('key3'));

        $this->assertEquals($u2_2, $repo->findByDomainAndTranslationKey('domain2', 'key2'));

        $this->assertEmpty($repo->findByDomain('non-existing domain'));
        $this->assertEmpty($repo->findByTranslationKey('non-existing key'));
        $this->assertFalse($repo->findByDomainAndTranslationKey('non-existing domain', 'non-existing key'));
    }
}