<?php
	namespace App\Presenters;

	use FrontModule\Newsletter;

	use Nette\Latte\Macros\MacroSet;

	use Nette\Latte\Engine;

	use WebLoader\Nette\JavaScriptLoader;

	use WebLoader\Nette\CssLoader;

	use WebLoader\Filter\LessFilter;

	use WebLoader\Compiler;

	use WebLoader\FileCollection;

	use Nette,
		App\Model;
	
	
	/**
	 * Base presenter for all application presenters.
	 */
	abstract class BasePresenter extends Nette\Application\UI\Presenter {
		public $model;		
		public $presenterName;
		
		public function startup () {
			parent::startup();
			
			$this->model = $this->context->model;
			$this->presenterName = $this->presenter->name;
		}
		
		public function templatePrepareFilters($template) {
			self::registerMacros($template);
		}
		
		public static function registerMacros ($template) {
			$latte = new Engine();
			$set = new MacroSet($latte->compiler);
				
			$set->addMacro('ifCurrentIn', 'if (in_array(trim("$presenter->presenterName:$presenter->action"), $presenter->formatCurrent(%node.array))):', 'endif');
				
			$template->registerFilter($latte);
		}
		
		protected function cssFileCollection ($subdir = NULL) {
			$www = $this->context->parameters['wwwDir'] . '/css' . ($subdir ? '/' . $subdir : '');
			$collection = new FileCollection($www, [ 'css', 'less' ]);
			return $collection;
		}
		
		protected function createComponentCss ($name) {
			$compiler = Compiler::createCssCompiler($this->cssFileCollection(), $this->context->parameters['wwwDir'] . '/webtemp');
			$compiler->setJoinFiles($this->context->parameters['productionMode']);
			$compiler->addFileFilter(new LessFilter());
		
			return new CssLoader($compiler, $this->template->basePath . '/webtemp');
		}
		
		protected function jsFileCollection ($subdir = NULL) {
			$www = $this->context->parameters['wwwDir'] . '/js' . ($subdir ? '/' . $subdir : '');
			$collection = new FileCollection($www, [ 'js' ]);
			return $collection;
		}
		
		protected function createComponentJs ($name) {
			$compiler = Compiler::createJsCompiler($this->jsFileCollection(), $this->context->parameters['wwwDir'] . '/webtemp');
			$compiler->setJoinFiles($this->context->parameters['productionMode']);
		
			return new JavaScriptLoader($compiler, $this->template->basePath . '/webtemp');
		}
		
		public function actionOut () {
			$this->getUser()->logout();
			$this->flashMessage('Odhlášení proběhlo v pořádku');
			$this->redirect('Sign:in');
		}
		
		public function formatCurrent ($array) {
			foreach ($array as $key => $val) {
				if (preg_match('/(.*):(.*)/', $val, $params)) {
					$array[$key] = $params[1].":".($params[2] == '*' ? $this->action : $params[2]);
				}
			}
				
			return $array;
		}
	}
