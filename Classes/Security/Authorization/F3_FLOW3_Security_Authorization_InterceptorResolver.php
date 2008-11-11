<?php
declare(ENCODING = 'utf-8');
namespace F3::FLOW3::Security::Authorization;

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

/**
 * @package FLOW3
 * @subpackage Security
 * @version $Id$
 */

/**
 * The security interceptor resolver. It resolves the class name of a security interceptor based on names.
 *
 * @package FLOW3
 * @subpackage Security
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class InterceptorResolver {

	/**
	 * @var F3::FLOW3::Object::ManagerInterface The object manager
	 */
	protected $objectManager;

	/**
	 * Constructor.
	 *
	 * @param F3::FLOW3::Object::ManagerInterface $objectManager The object manager
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function __construct(F3::FLOW3::Object::ManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Resolves the class name of a security interceptor. If a valid interceptor class name is given, it is just returned.
	 *
	 * @param string $name The (short) name of the interceptor
	 * @return string The class name of the security interceptor, NULL if no class was found.
	 * @throws F3::FLOW3::Security::Exception::NoInterceptorFound
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function resolveInterceptorClass($name) {
		$resolvedClassName = '';

		$nameIsClassName = $this->objectManager->getCaseSensitiveObjectName($name);
		if ($nameIsClassName) $resolvedClassName = $nameIsClassName;

		$extendedNameIsClassName = $this->objectManager->getCaseSensitiveObjectName('F3::FLOW3::Security::Authorization::Interceptor::' . $name);
		if ($extendedNameIsClassName) $resolvedClassName = $extendedNameIsClassName;

		if ($resolvedClassName != '') return $resolvedClassName;

		throw new F3::FLOW3::Security::Exception::NoInterceptorFound('A security interceptor with the name: "' . $name . '" could not be resolved.', 1217154134);
	}
}
?>