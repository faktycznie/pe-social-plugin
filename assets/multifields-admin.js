/*
* Author: artur.kaczmarek@pixelemu.com
* Version: 1.00
*/

(function($) {

	"use strict";

	var PEwidget = function( id ) {

		if ( 'undefined' === typeof id || ! id ) {
			return;
		}

		var self = this;
		this.events_set = false;

		this.id = id;
		this.group = $(this.id).find('.pe-fields-group').first();
		this.data_holder = $(this.id).find('.data-holder');
		this.mediaUploader;

		this.showtime = function () {

			if( self.events_set === true ) {
				return;
			}

			//console.log('loaded ' + self.id);

			var add_btn = $(self.id).find('.add-new');
			var save_btn = $(self.id).find('.save-item');
			var edit_btn = $(self.id).find('.edit-item');
			var remove_btn = $(self.id).find('.remove-item');
			var cancel_btn = $(self.id).find('.cancel-item');
			var media_btn = $(self.id).find('.media-item');

			add_btn.click(function(e){
				e.preventDefault();
				self.clearForm();
				self.toggleForm(1);
				save_btn.removeAttr('data-edit-item');
			});

			save_btn.click(function(e){
				e.preventDefault();
				var editID = save_btn.attr('data-edit-item');

				if( editID && $.isNumeric(editID) ) {
					self.saveData(editID);
				} else {
					self.saveData();
				}

				//trigger save widget on save item
				self.saveWidget();

			});

			remove_btn.click(function(e){
				e.preventDefault();
				var id = $(this).attr('data-item');
				self.removeItem(id);
			});

			edit_btn.click(function(e){
				e.preventDefault();
				var id = $(this).attr('data-item');
				//add save param
				save_btn.attr('data-edit-item', id);
				self.editItem(id);
			});

			cancel_btn.click(function(e){
				e.preventDefault();
				self.clearForm();
				self.toggleForm(0);
				save_btn.removeAttr('data-edit-item');
			});

			media_btn.click(function(e){
				e.preventDefault();
				self.mediaUpload( $(this) );
			});

			self.events_set = true;

		};

		this.showtime();

	};

	PEwidget.prototype = {
		constructor: PEwidget,

		getCurrentData: function () {
			var value = $(this.id).find('.data-holder').val();
			return $.parseJSON(value); //json to object
		},

		getFields: function () {
			return this.group.find('[name]');
		},

		getData: function () {
			var fieldsData = new Array();
			var fields = this.getFields();
			var fieldObj = {};

			$.each(fields, function(i) {
				var name = $(this).attr('name');
				var value = $(this).val();
				var checked = $(this).prop('checked') ? true : false;
				var type = $(this).attr('type');
				if( type == 'radio' || type == 'checkbox' ) {
					if( !checked ) {
						value = false;
					}
				}
				fieldObj = {
					name: name,
					value: value,
					type: type,
				}
				fieldsData.push(fieldObj);
			});

			return fieldsData;
		},

		setDataField: function( data ) {
			data = JSON.stringify(data);
			this.data_holder.val(data);
		},

		saveData: function ( id ) {
			var dataArray = new Array();
			var currentData = this.getCurrentData();
			var data = this.getData();

			if( typeof currentData != 'undefined' && currentData ) {
				dataArray = currentData;
			}

			if( id != 'undefined' && id && dataArray[id] != 'undefined' && dataArray[id] ) {
				//override element
				dataArray[id] = data;
			} else {
				//add new element
				dataArray.push(data);
			}

			this.setDataField(dataArray);
		},

		saveWidget: function () {
			$(this.id).fadeTo( 'slow', 0.5 ).css('pointer-events', 'none');
			$(this.id).closest('.widget-inside').find('.widget-control-save').click();
		},

		removeItem: function ( id ) {
			var dataArray = this.getCurrentData();
			dataArray.splice(id, 1);
			this.setDataField(dataArray);
			//trigger save widget on save item
			this.saveWidget();
		},

		editItem: function ( id ) {

			var currentData = this.getCurrentData();
			var data = currentData[id];
			var fields = this.getFields();

			this.clearForm();
			this.toggleForm(1);

			$.each(fields, function(i) {
				var item_data = data[i]['value'];
				var checked = ( data[i]['checked'] ) ? true : false ;
				var tag = $(this).prop('tagName');
				var type = $(this).attr('type');

				if( tag == 'SELECT' ) {
					$(this).find('option[value="' + item_data + '"]').attr('selected', 'selected');
				} else if ( type == 'radio' || type == 'checkbox' ) {
					if( item_data ) {
						$(this).attr('checked', 'checked');
					}
				} else {
					$(this).val(item_data);
				}

			});
		},

		clearForm: function () {

			var fields = this.getFields();
			$.each(fields, function() {

				var tag = $(this).prop('tagName');
				var type = $(this).attr('type');
				if( tag == 'SELECT' ) {
					$(this).find('option').removeAttr('selected');
				} else if ( type == 'radio' || type == 'checkbox' ) {
					$(this).removeAttr('checked');
				} else {
					$(this).val('');
				}
			});
		},

		toggleForm: function ( index ) {

			if( index == 'undefined' && !index ) {
				index = 3;
			}

			var form = this.group;

			if( index == 0 ) {
				form.removeClass('active');
				form.hide(400);
			} else if (index == 1) {
				form.addClass('active');
				form.show(400);
			} else {
				form.toggleClass('active');
				form.slideToggle();
			}
		},

		mediaUpload: function ( btn ) {

			var input = btn.prev();
			var btn_name = btn.val();
			var self = this;

			// If the uploader object has already been created, reopen the dialog
			if ( self.mediaUploader ) {
				self.mediaUploader.open();
				return;
			}
			// Extend the wp.media object
			self.mediaUploader = wp.media.frames.file_frame = wp.media({
				title: btn_name,
				button: {
				text: btn_name
			}, multiple: false });

			// When a file is selected, grab the URL and set it as the text field's value
			self.mediaUploader.on('select', function() {
				var attachment = self.mediaUploader.state().get('selection').first().toJSON();
				input.val(attachment.url);
			});
			// Open the uploader dialog
			self.mediaUploader.open();
		},
	}

	window.PEwidget = PEwidget;

})(jQuery);
