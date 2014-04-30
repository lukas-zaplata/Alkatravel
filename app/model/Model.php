<?php
// 	namespace Models;

	/**
	 * Základní třída modelu
	 */

	class Model extends Nette\Object {
		public $database;
		
		/**
		 * Konstruktor pro připojení k db
		 */
		public function __construct(Nette\Database\Context $database) {
			$this->database = $database;
		}
		
		/**
		 * Model pro získání uživatelů
		 */
		public function getUsers () {
			return $this->database->table('users');
		}
		
		public function getUsersCategories () {
			return $this->database->table('users_categories');
		}
		
		/**
		 * Model pro získání galerií
		 */
		public function getGalleries () {
			return $this->database->table('galleries');
		}
		
		/**
		 * Model pro získání obrázků galerií
		 */
		public function getGalleriesImages () {
			return $this->database->table('galleries_images');
		}
		
		public function getStatistics() {
			return $this->database->table('statistics');
		}
		
		public function getEmails () {
			return $this->database->table('emails');
		}
		
		public function getEmailsQueue () {
			return $this->database->table('emails_queue');
		}
	}