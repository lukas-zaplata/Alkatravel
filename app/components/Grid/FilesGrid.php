<?php
	namespace AdminModule;
	
	use NiftyGrid\DataSource\NDataSource;
	
	use Nette\Utils\Html;
	
	use NiftyGrid\Grid;
	
	class FilesGrid extends Grid {
		public $data;
	
		public function __construct($data) {
			parent::__construct();
				
			$this->data = $data;
		}
	
		public function configure($presenter) {
			$dataSource = new NDataSource($this->data);
			$this->setDataSource($dataSource);
				
			$self = $this;
			/*	
			$this->addColumn('position')
				->setWidth('20px')
				->setTextEditable();
			$this->addColumn('visibility')
				->setWidth('20px')
				->setRenderer(function($row) use($self) {return Html::el('a')->href($self->presenter[$self->presenter->action]->link('Visibility!', array($self->presenter->id, $row['id'], $row['visibility'] == 0 ? 0 : 1)))->addClass($row['visibility'] == 0 ? 'invisible' : 'visible')->addClass('grid-ajax');});
			$this->addColumn('highlight')
				->setWidth('20px')
				->setRenderer(function($row) use($self) {return Html::el('a')->href($self->presenter[$self->presenter->action]->link('Highlight!', array($self->presenter->id, $row['id'], $row['highlight'] == 0 ? 0 : 1)))->addClass($row['highlight'] == 0 ? 'invisible' : 'visible')->addClass('grid-ajax');});
			*/
			if ($this->presenter->action == 'gallery') {
				$this->addColumn('id')
					->setWidth('50px')
					->setRenderer(function($row) use($self) {
						$path = $self->presenter->context->httpRequest->url->basePath;
						return Html::el('img')->addAttributes(array('src' => preg_replace('~/$~', '', $path).'/files/'.$self->presenter['gallery']->dimension.'_g'.$row['galleries_id'].'-'.$row['name']));
					})
					->setSortable(false);
			}
			$this->addColumn('name', 'název');
			/*$this->addColumn('title', 'titulek')
				->setWidth('250px')
				->setTextEditable();
				*/			
			$this->setTemplate(APP_DIR.'/templates/Grid/filesGrid.latte');
			$this->paginate = false;
			$this->setWidth('100%');
			$this->setDefaultOrder('position ASC');
				
			/*$this->addButton(Grid::ROW_FORM, "Rychlá editace")
				->setClass("fast-edit");
			if ($this->presenter->action == 'gallery') {
				$this->addButton('edit', 'Editovat')
					->setClass('edit')
					->setLink(function($row) use ($self){
						return $self->presenter[$self->presenter->action]->link('EditImage!', array($row['id']));
					})
					->setAjax(false);
			}
			*/
			$this->addButton('delete', 'Smazat')
				->setClass('fa fa-trash-o')
				->setLink(function($row) use ($self){
					return $self->presenter[$self->presenter->action]->link('Delete!', array($self->presenter->id, $row['id']));
				})
				->setConfirmationDialog(function($row){return "Opravdu odstranit obrázek ".$row['name']."?";});
			/*
			$this->addAction("visible","Zviditelnit")
				->setCallback(function($id) use ($self){
					return $self->presenter[$self->presenter->action]->handleVisibility($self->presenter->id, $id, 0);
				});
				
			$this->addAction("invisible","Skrýt")
				->setCallback(function($id) use ($self){
					return $self->presenter[$self->presenter->action]->handleVisibility($self->presenter->id, $id, 1);
				});
				
			$this->addAction("highlight","Zvýraznit")
				->setCallback(function($id) use ($self){
					return $self->presenter[$self->presenter->action]->handleHighlight($self->presenter->id, $id, 0);
				});
	
			$this->addAction("unhighlight","Odzvýraznit")
				->setCallback(function($id) use ($self){
					return $self->presenter[$self->presenter->action]->handleHighlight($self->presenter->id, $id, 1);
				});
			*/	
			$this->addAction("delete","Smazat")
				->setCallback(function($id) use ($self){
					return $self->presenter[$self->presenter->action]->handleDelete($self->presenter->id, $id);
				})
				->setConfirmationDialog("Opravdu smazat všechny vybrané obrázky?");
				
			$this->setRowFormCallback(function ($values) use ($self) {
				$row = $self->data->wherePrimary($values['id']);
	
				$self->presenter->lastEdited->rows[] = $values['id'];
	
				unset($values['id']);
				$row->update($values);
			});
		}
	}