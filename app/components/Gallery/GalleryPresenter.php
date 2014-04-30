<?php		
	use Nette\Utils\Finder;

use AdminModule\FilesGrid;

use Nette\Application\UI\Control;

	use Nette\Image;

	use Nette\Application\UI\Form;

	class GalleryPresenter extends Control {
		public $gallery;
		public $images;
		public $image;
		public $article;
		public $dimension;
		public $dimensions;
		public $edit;
		
		public function __construct($parent, $name) {
			parent::__construct($parent, $name);
			
			$this->getImages();
		}
		
		public function getImages () {
			$this->gallery = $this->presenter->model->getGalleries()->wherePrimary($this->presenter->id)->fetch();
			$this->images = $this->presenter->model->getGalleriesImages()->where('galleries_id', $this->presenter->id)->order('position ASC');
			
			$this->dimension = '100x100';
			$this->dimensions = array('100x100');	
		}
		
		public function getDimensions() {
			return $this->presenter->model->getSectionsThumbs()->where('sections_id', $this->presenter->sid);
		}
		
		public function render () {
			if ($this->edit) {
				if ($this->edit !== true) {
					$this->template->setFile(__DIR__.'/position.latte');
				}
				else {
					$this->template->setFile(__DIR__.'/editImage.latte');
				}
			}
			else {
				$this->template->setFile(__DIR__.'/view.latte');
			}
			
			$this->template->images = $this->images;
			$this->template->thumb = $this->dimension;
			
			$this->template->render();
		}
		
		public function handleEditImage ($id) {
			$this->presenter->section = $this->presenter->sid != 0 ? $this->presenter->model->getSections()->wherePrimary($this->presenter->sid)->fetch() : $this->presenter->model->getShopSettings()->order('id ASC')->fetch();
			$this->image = $this->presenter->model->getGalleriesImages()->wherePrimary($id)->fetch();
			$this->presenter->id = $id;
			
			$this->edit = true;
			
			$this->template->image = $this->image;
			$this->template->dimensions = $this->presenter->model->getSectionsThumbs()->where('sections_id', $this->presenter->sid);
		}
		
		public function editGallery ($form) {
			$values = $form->getValues();
			
			$this->presenter->model->getGalleries()->wherePrimary($this->presenter->id)->update($values);
			
			$this->flashMessage('Galerie byla upravena');
			$this->redirect('this');
		}
		
		public function createComponentCropForm () {
			$form = new Form();
			
			$form->addHidden('left');
			$form->addHidden('top');
			$form->addHidden('width');
			$form->addHidden('height');
			$form->addHidden('originalWidth');
			$form->addHidden('originalHeight');
			
			$form->addGroup('')
				->setOption('container', 'fieldset class="submit"');
			$form->addSubmit('crop', 'Oříznout');
			
			$form->addHidden('id', $this->presenter->id);
			
			$form->onSuccess[] = callback ($this, 'cropImage');
			
			return $form;
		}
		
		public function cropImage ($form) {
			$values = $form->getValues();
			
			$this->image = $this->presenter->model->getGalleriesImages()->wherePrimary($values['id'])->fetch();
			
			$thumb = Image::fromFile(WWW_DIR . '/files/galleries/g'.$this->image->galleries->id.'-'.$this->image->name);
			$thumb->crop($values['left'], $values['top'], $values['width'], $values['height']);
			$thumb->resize(($values['originalWidth'] >= $values['originalHeight'] ? $values['originalWidth'] : null), ($values['originalHeight'] >= $values['originalWidth'] ? $values['originalHeight'] : null));
			$thumb->save(WWW_DIR . '/files/galleries/'.$values["originalWidth"].'x'.$values["originalHeight"].'_g'.$this->image->galleries.'-'. $this->image->name);
			
// 			$this->invalidateControl('thumbs');
			$this->presenter->flashMessage('Oříznutí obrázku bylo změněno');
			$this->presenter->redirect('this');
		}
		
		public function handleUpload () {
			$httpRequest = $this->presenter->context->getService('httpRequest');
			
			$basePath = $httpRequest->url->basePath;
			
			$files = $httpRequest->getFiles();
			
			foreach ($files as $file) {
				$lastPosition = $this->presenter->model->getGalleriesImages()->where('galleries_id', $this->presenter->id)->order('position DESC')->fetch();
				
				$values['name'] = $file->getSanitizedName();
				$values['galleries_id'] = $this->presenter->id;
				$values['position'] = !$lastPosition ? 0 : $lastPosition->position+1; 
				
				$original = $file->move(WWW_DIR . '/files/temp_g'.$this->presenter->id.'-' . $file->getSanitizedName());
				
				$image = Image::fromFile($original);
				$image->resize(1024, 1024, Image::SHRINK_ONLY);
				$image->save(WWW_DIR . '/files/g'.$this->presenter->id.'-' . $file->getSanitizedName());
				
				unlink($original);
				
				$thumb = Image::fromFile(WWW_DIR . '/files/g'.$this->presenter->id.'-' . $file->getSanitizedName());
					
				$thumb->resize(100, 100, Image::SHRINK_ONLY);
				$thumb->save(WWW_DIR . '/files/100x100_g'.$this->presenter->id.'-' . $file->getSanitizedName(), 90);			

				if (!$this->presenter->model->getGalleriesImages()->where(array('galleries_id' => $this->presenter->id, 'name' => $values['name']))->fetch()) {
					$this->presenter->model->getGalleriesImages()->insert($values);
				}
			}
		}
		
		public function handleVisibility ($id, $imageID, $vis) {
			$vis = $vis == 1 ? 0 : 1;
			$this->presenter->model->getGalleriesImages()->where('id', $imageID)->update(array("visibility" => $vis));
				
			$this->presenter->flashMessage('Nastavení zobrazení obrázku změněno!');
		}
		
		public function handleHighlight($id, $imageID, $vis) {
			$vis = $vis == 1 ? 0 : 1;
			$this->presenter->model->getGalleriesImages()->where('id', $imageID)->update(array("highlight" => $vis));
		
			$this->presenter->flashMessage('Nastavení zvýraznění obázku změněno!');
		}
		
		public function handleDelete ($id, $imageID) {			
			$ids = (array)$imageID;
			$dir = WWW_DIR . '/files/';
			
			foreach ($ids as $val) {
				$image = $this->presenter->model->getGalleriesImages()->wherePrimary($val)->fetch();
				
				foreach (Finder::findFiles('*g'.$id.'-'.$image->name)->in($dir) as $file) {
					unlink($file->getPathName());
				}
			}
			
			$image = $this->presenter->model->getGalleriesImages()->where('id', array_values($ids))->delete();
		}
		
		public function handlePosition () {
			$this->edit = 'position';
		}
		
		public function handleChangeOrder () {
			$positions = $_GET['positions'];
				
			foreach ($positions as $key => $value) {
				$values['position'] = $key;
				$this->presenter->model->getGalleriesImages()->wherePrimary($value)->update($values);
			}
				
			$this->presenter->flashMessage('Pořadí bylo změněno');
		}
		
		public function createComponentGrid () {
			return new FilesGrid($this->images);
		}
	}