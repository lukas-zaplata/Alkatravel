<?php
	namespace AdminModule;

	use NiftyGrid\DataSource\NDataSource;

	use Nette\Utils\Html;

	use NiftyGrid\Grid;

	class MailingGrid extends Grid {
		public $data;
		
		public function __construct($data) {
			parent::__construct();
			
			$this->data = $data;
		}
		
		public function configure($presenter) {
			$dataSource = new NDataSource($this->data/*->where('pid', $this->pid)*/);
			$this->setDataSource($dataSource);
			
			$self = $this;
			
			$this->addColumn('name', 'Jméno')
				->setTextEditable()
				->setTextFilter();
			
			$this->addColumn('subject', 'Předmět')
				->setTextEditable()
				->setTextFilter();
			
			$this->addColumn('date', 'Datum')
				->setRenderer(function ($row) {
					return $row['date']->format('j.n.Y');
				});
				
			$this->addColumn('queue', 'Stav', '50px')
				->setRenderer(function ($row) use ($self) {
					if ($self->presenter->model->getEmailsQueue()->where('emails_id', $row['id'])->where('date >= ?', date('Y-m-d H:i'))->fetch()) {
						return Html::el('img')->src($self->presenter->context->httpRequest->url->basePath.'/adminModule/images/sending.gif')->style('width', 'auto');
					}
				});
			
			/*$this->addButton(Grid::ROW_FORM, "Rychlá editace")
				->setClass("fast-edit");*/
			
			$this->addButton('edit', 'Editovat')
				->setClass('fa fa-pencil')
				->setLink(function($row) use ($self){return $self->presenter->link('Homepage:edit', array($row['id']));})
				->setAjax(false);
			
			$this->addButton('preview', 'Náhled')
				->setClass('email')
				->setLink(function($row) use ($self){return $self->presenter->link('Mailing:preview', array($row['id']));})
				->setAjax(false);
			
			$this->addButton('graph', 'Graf')
				->setClass('graph')
				->setLink(function ($row) use ($self) {return $self->presenter->link('Mailing:graph', array($row['id']));})
				->setAjax(false);
			
			$this->addButton('gallery', 'Galerie')
				->setClass('fa fa-camera')
				->setLink(function($row) use ($self){return $self->presenter->link('Homepage:gallery', array($row['galleries_id']));})
				->setAjax(false);
			
			$this->addButton('delete', 'Smazat')
				->setClass('fa fa-trash-o')
				->setLink(function($row) use ($self){return $self->presenter->link('Delete!', array($row['id']));})
				->setConfirmationDialog(function($row){return "Opravdu odstranit $row[name]?";});
			
			$this->setTemplate(APP_DIR.'/templates/Grid/grid.latte');
			$this->paginate = false;
			$this->setWidth('100%');
			
			$this->addAction("delete","Smazat")
				->setCallback(function($id) use ($self){return $self->presenter->handleDelete($id);})
				->setConfirmationDialog("Opravdu smazat všechny vybrané kampaně?");
			
			$this->setRowFormCallback(function ($values) use ($self) {
				$row = $self->data->find($values['id']);
			
				$self->presenter->lastEdited->rows[] = $values['id'];
			
				unset($values['id']);
				$row->update($values);
			});
		}
	}