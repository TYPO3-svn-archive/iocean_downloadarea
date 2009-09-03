jQuery.noConflict();

jQuery(window).load( function() {
	/**
	 * display plugin
	 */
	jQuery(".principal_downloadarea").show();

	/**
	 * function for deployment arborescence
	 */
	jQuery(".button").click( function() {
		if (jQuery(this).hasClass("plus")) {
			var tab_p = jQuery(this).attr("id").split('_');
			jQuery("#" + tab_p[0]).show();
			jQuery(this).addClass("minus");
			jQuery(this).removeClass("plus");
		} else {
			var tab_m = jQuery(this).attr("id").split('_');
			jQuery("#" + tab_m[0]).hide();
			jQuery(this).addClass("plus");
			jQuery(this).removeClass("minus");
		}
	});

	/**
	 * Allow recovery info in block left for block right
	 */
	jQuery(".JqueryDossier").click( function() {
		jQuery(".JqueryDossier").each( function() {
			jQuery(this).css("font-weight", "");
		});

		jQuery(this).css("font-weight", "bold");

		var tab = jQuery(this).attr("href").split(';');
		var chemin = tab[0];
		var num_page = tab[1];
		var num_sous_dossier = tab[2];
		var id = ".0_fichier_" + chemin;
		
		jQuery(".fichier").hide();
		var tab2 = jQuery(this).html().split(' ');
		
		
		var name_dir = "";
		
		
		if(num_sous_dossier != 0){
			if(jQuery("#"+chemin+"_button").hasClass("plus")){
				jQuery("#"+chemin).show();
				jQuery("#"+chemin+"_button").addClass("minus");
				jQuery("#"+chemin+"_button").removeClass("plus");
			}
		}
		if (jQuery(id).val() == "") {
			jQuery(id).show();
			jQuery(".pagination").val(0)
			jQuery(".chemin").val(jQuery(this).attr("href"));
			//jQuery("#title_directory").empty();
			//jQuery("#title_directory").append(name_dir);
			jQuery("#pagination_max").val(num_page);
			if (num_page < 2) {
				jQuery("#suiv_stable").show();
				jQuery("#suiv").hide();
			} else {
				jQuery("#suiv").show();
				jQuery("#suiv_stable").hide();
			}
			jQuery("#prec").hide();
			jQuery("#prec_stable").show();
		} else {
			jQuery(".default_fichier").show();
			if(num_sous_dossier != 0){
				jQuery(".default_directory").show();
			}
			jQuery("#suiv").hide();
			jQuery("#prec").hide();
			jQuery("#suiv_stable").hide();
			jQuery("#prec_stable").hide();
		}
		jQuery("#singleView").empty();
		return false;

	});

	/**
	 * Allow navigate with pagination
	 */
	jQuery(".JqueryPagination").click( function() {
		if (jQuery(this).attr("href") == "suiv") {
			var num = jQuery(".pagination").val();
			num++;
			var tab = jQuery(".chemin").val().split(';');
			var chemin = tab[0];
			var num_page = tab[1];
			var id = "." + num + "_fichier_" + chemin;
			if (jQuery(id).val() == "") {
				jQuery(".pagination").val(num);
				jQuery(".fichier").hide();
				jQuery(id).show();
				jQuery("#pagination_max").val(num_page);
				if (num == num_page - 1) {
					jQuery("#suiv").hide();
					jQuery("#suiv_stable").show();
				}
				if (num == 1) {
					jQuery("#prec").show();
					jQuery("#prec_stable").hide();
				}
			}
		} else {
			var num = jQuery(".pagination").val();
			num--;
			var tab = jQuery(".chemin").val().split(';');
			var chemin = tab[0];
			var num_page = tab[1];
			var id = "." + num + "_fichier_" + chemin;
			if (jQuery(id).val() == "") {
				jQuery(".pagination").val(num);
				jQuery(".fichier").hide();
				jQuery(id).show();
				jQuery("#pagination_max").val(num_page);
				if (num == 0) {
					jQuery("#prec").hide();
					jQuery("#prec_stable").show();
				}
				if (num == num_page - 2) {
					jQuery("#suiv").show();
					jQuery("#suiv_stable").hide();
				}
			}
		}
		jQuery("#singleView").empty();
		return false;
	});

	jQuery("a.AjaxSingleView").click( function() {
		jQuery.ajax( {
			type :"POST",
			url :$(this).attr("href"),
			success : function(retour) {
				jQuery("#singleView").empty().append(retour);
			}
		});
		return false;
	});

});