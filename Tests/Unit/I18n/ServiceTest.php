<?php
namespace TYPO3\FLOW3\Tests\Unit\I18n;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 */

/**
 * Testcase for the Locale Service class.
 *
 */
class ServiceTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	/**
	 * @return void
	 */
	public function setUp() {
		\vfsStreamWrapper::register();
		\vfsStreamWrapper::setRoot(new \vfsStreamDirectory('Foo'));
	}

	/**
	 * @test
	 */
	public function getLocalizedFilenameReturnsCorrectlyLocalizedFilename() {
		$desiredLocale = new \TYPO3\FLOW3\I18n\Locale('en_GB');
		$parentLocale = new \TYPO3\FLOW3\I18n\Locale('en');
		$localeChain = array('en_GB' => $desiredLocale, 'en' => $parentLocale);
		$filename = 'vfs://Foo/Bar/Public/images/foobar.png';
		$expectedFilename = 'vfs://Foo/Bar/Public/images/foobar.en.png';

		mkdir(dirname($filename), 0777, TRUE);
		file_put_contents($expectedFilename, 'FooBar');

		$service = $this->getMock('TYPO3\FLOW3\I18n\Service', array('getLocaleChain'));
		$service->expects($this->atLeastOnce())->method('getLocaleChain')->with($desiredLocale)->will($this->returnValue($localeChain));

		$result = $service->getLocalizedFilename($filename, $desiredLocale);
		$this->assertEquals($expectedFilename, $result);
	}

	/**
	 * @test
	 */
	public function getLocalizedFilenameReturnsCorrectFilenameIfExtensionIsMissing() {
		mkdir('vfs://Foo/Bar/Public/images/', 0777, TRUE);
		file_put_contents('vfs://Foo/Bar/Public/images/foobar.en_GB', 'FooBar');

		$filename = 'vfs://Foo/Bar/Public/images/foobar';
		$expectedFilename = 'vfs://Foo/Bar/Public/images/foobar.en_GB';

		$service = new \TYPO3\FLOW3\I18n\Service();

		$result = $service->getLocalizedFilename($filename, new \TYPO3\FLOW3\I18n\Locale('en_GB'), TRUE);
		$this->assertEquals($expectedFilename, $result);
	}

	/**
	 * @test
	 */
	public function getLocalizedFilenameReturnsCorrectFilenameInStrictMode() {
		mkdir('vfs://Foo/Bar/Public/images/', 0777, TRUE);
		file_put_contents('vfs://Foo/Bar/Public/images/foobar.en_GB.png', 'FooBar');

		$filename = 'vfs://Foo/Bar/Public/images/foobar.png';
		$expectedFilename = 'vfs://Foo/Bar/Public/images/foobar.en_GB.png';

		$service = new \TYPO3\FLOW3\I18n\Service();

		$result = $service->getLocalizedFilename($filename, new \TYPO3\FLOW3\I18n\Locale('en_GB'), TRUE);
		$this->assertEquals($expectedFilename, $result);
	}

	/**
	 * @test
	 */
	public function getLocalizedFilenameReturnsOriginalFilenameInStrictModeIfNoLocalizedFileExists() {
		$filename = 'vfs://Foo/Bar/Public/images/foobar.png';

		$service = new \TYPO3\FLOW3\I18n\Service();

		$result = $service->getLocalizedFilename($filename, new \TYPO3\FLOW3\I18n\Locale('pl'), TRUE);
		$this->assertEquals($filename, $result);
	}

	/**
	 * @test
	 */
	public function getLocalizedFilenameReturnsOriginalFilenameIfNoLocalizedFileExists() {
		$filename = 'vfs://Foo/Bar/Public/images/foobar.png';
		$desiredLocale = new \TYPO3\FLOW3\I18n\Locale('de_CH');
		$localeChain = array('de_CH' => $desiredLocale, 'en' => new \TYPO3\FLOW3\I18n\Locale('en'));

		$service = $this->getMock('TYPO3\FLOW3\I18n\Service', array('getLocaleChain'));
		$service->expects($this->atLeastOnce())->method('getLocaleChain')->with($desiredLocale)->will($this->returnValue($localeChain));

		$result = $service->getLocalizedFilename($filename, $desiredLocale);
		$this->assertEquals($filename, $result);
	}

	/**
	 * @test
	 */
	public function initializeCorrectlyGeneratesAvailableLocales() {
		mkdir('vfs://Foo/Bar/Private/', 0777, TRUE);
		foreach (array('en', 'sr_Cyrl_RS', 'en_GB', 'sr') as $localeIdentifier) {
			file_put_contents('vfs://Foo/Bar/Private/foobar.' . $localeIdentifier . '.baz', 'FooBar');
		}

		$mockPackage = $this->getMock('TYPO3\FLOW3\Package\PackageInterface');
		$mockPackage->expects($this->any())->method('getPackageKey')->will($this->returnValue('Bar'));

		$mockPackageManager = $this->getMock('TYPO3\FLOW3\Package\PackageManagerInterface');
		$mockPackageManager->expects($this->any())->method('getActivePackages')->will($this->returnValue(array($mockPackage)));

		$mockLocaleCollection = $this->getMock('TYPO3\FLOW3\I18n\LocaleCollection');
		$mockLocaleCollection->expects($this->exactly(4))->method('addLocale');

		$mockSettings = array('i18n' => array('defaultLocale' => 'sv_SE', 'fallbackRule' => array()));

		$mockCache = $this->getMock('TYPO3\FLOW3\Cache\Frontend\VariableFrontend', array(), array(), '', FALSE);
		$mockCache->expects($this->once())->method('has')->with('availableLocales')->will($this->returnValue(FALSE));

		$service = $this->getAccessibleMock('TYPO3\FLOW3\I18n\Service', array('dummy'));
		$service->_set('localeBasePath', 'vfs://Foo/');
		$service->injectPackageManager($mockPackageManager);
		$service->injectLocaleCollection($mockLocaleCollection);
		$service->injectSettings($mockSettings);
		$service->injectCache($mockCache);
		$service->initialize();
	}
}

?>