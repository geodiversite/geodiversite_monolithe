(function() {
// Plugin Leaflet L.Map.Geodiv
L.Map.Geodiv = L.Map.extend({
	
	includes: L.Mixin.Events,
	
	options:{
		gis_layers: L.gisConfig.gis_layers,
		default_layer: L.gisConfig.default_layer,
		affiche_layers: L.gisConfig.affiche_layers,
		layersControl: true,
		zoomControl: false,  // Pas tout de suite pour permettre l'ajout du control layers avant
		scaleControl: true,
		worldCopyJump: true,
		layersControlOptions: {position: 'topleft', collapsed: false},
		loadData: true,
		dataUrl: null,
		getInfowindowUrl: L.gisConfig.getInfowindowUrl,
		openInfowindow: null,
		clusterOptions: {
			maxClusterRadius: 70,
			spiderfyOnMaxZoom: false,
			showCoverageOnHover: false,
			chunkedLoading: true,
			chunkInterval: 500,
			iconCreateFunction: function(cluster) {
				var childCount = cluster.getChildCount();
				return new L.DivIcon({ html: '<div><span>' + ((childCount == 1) ? '' : childCount) + '</span></div>', className: 'marker-geodiv marker-clusterer', iconSize: new L.Point(40, 40) });
			}
		}
	},
	
	initialize: function (id,options) {
		L.Util.setOptions(this, options);
		
		L.Map.prototype.initialize.call(this, id, options);
		
		this.populateTileLayers(this.options.affiche_layers);
		
		this.attributionControl.setPrefix('');
		
		if (this.options.scaleControl)
			this.scaleControl = L.control.scale().addTo(this);
		
		this.zoomControl = new L.Control.Zoom().addTo(this);
		this.addControl(new L.Control.FullScreen());
		
		this.infowindow = new L.Popup({className: 'geol_window', maxWidth: 350});
		
		this.initCluster();
		
		if (this.options.loadData && this.options.dataUrl)
			this.loadData();
		
		this.on('zoomend', function(e) {
			if (this.infowindow)
				this.infowindow._close();
		});
	},

	populateTileLayers: function (tilelayers) {
		// Fond de carte par défaut
		var default_layer = this.createTileLayer(this.options.default_layer);
		this.addLayer(default_layer);
		// Fonds de carte supplémentaires
		if (this.options.affiche_layers.length>1){
			var layers_control = new L.Control.Layers('','',this.options.layersControlOptions);
			layers_control.addBaseLayer(default_layer,this.options.gis_layers[this.options.default_layer]["nom"]);
			for(var l in this.options.affiche_layers){
				if (this.options.affiche_layers[l]!==this.options.default_layer){
					var layer = this.createTileLayer(this.options.affiche_layers[l]);
					if (typeof layer!=="undefined")
						layers_control.addBaseLayer(layer,this.options.gis_layers[this.options.affiche_layers[l]]["nom"]);
				}
			}
			this.addControl(layers_control);
			// ajouter l'objet du controle de layers à la carte pour permettre d'y accéder depuis le callback
			this.layersControl = layers_control;
			// classe noajax sur le layer_control pour éviter l'ajout de hidden par SPIP
			L.DomUtil.addClass(layers_control._form,'noajax');
		}
	},

	createTileLayer: function (name) {
		var layer;
		if (typeof this.options.gis_layers[name]!=="undefined")
			eval("layer=new "+ this.options.gis_layers[name]["layer"] +";");
		return layer;
	},
	
	loadData: function () {
		var me = this;
		$.getJSON(me.options.dataUrl, function(data) {
			if (data){
				me.parseData(data);
				me.fire('ready');
			}
		});
	},

	parseData: function (data) {
		var me = this;
		markers = [];
		$.each(data.features, function(i,item){
			if (item.geometry.coordinates[0]) {
				var latlng = new L.LatLng(item.geometry.coordinates[1],item.geometry.coordinates[0]);
				var marker = new L.Marker(latlng);
				if (item.properties.icon) {
					marker.setIcon(new L.Icon({
						iconUrl: item.properties.icon,
						iconSize: new L.Point(32,32),
						iconAnchor: new L.Point(16,16)
					}));
				}
				marker.id = item.id;
				markers.push(marker);
			}
		});
		me.markerCluster.addLayers(markers);
	},
	
	getInfowindow: function(ids,latlng) {
		var me = this;
		$.ajax({
			url: me.options.getInfowindowUrl,
			data: { id_article : ids },
			success: function(data){
				me.infowindow.setContent(data);
				me.infowindow.setLatLng(latlng);
				me.openPopup(me.infowindow);
			}
		});
	},
	
	initCluster: function () {
		var me = this;
		// si le chunkloading est actif, créer les conteneurs pour la barre de chargement et déclarer la fonction associée pour la mise à jour
		if (me.options.clusterOptions.chunkedLoading) {
			me.progress = L.DomUtil.create('div', 'leaflet-bar leaflet-progress', me.getContainer());
			me.progressBar = L.DomUtil.create('div', 'leaflet-progress-bar', me.progress);
			me.options.clusterOptions.chunkProgress = function updateProgressBar(processed, total, elapsed) {
				if (elapsed > 1000) {
					me.progress.style.display = 'block';
					me.progressBar.style.width = Math.round(processed/total*100) + '%';
				}
				if (processed === total) {
					me.progress.style.display = 'none';
				}
			}
		}
		// init du cluster
		me.markerCluster = new L.MarkerClusterGroup(me.options.clusterOptions).addTo(me);
		// lors du clic sur un marker, ouvrir sa popup
		me.markerCluster.on('click', function (e) {
			me.getInfowindow(e.layer.id,e.layer.getLatLng());
		});
		// lors du  clic sur un cluster, si le zoom est au max ouvrir une popup multiple
		me.markerCluster.on('clusterclick', function (e) {
			var markers = e.layer.getAllChildMarkers();
			if (markers.length > 1) {
				// si on est au zoom maxi de la carte
				if (me.getMaxZoom() <= me.getZoom()) {
					// récupérer les id des markers du cluster
					var markers_ids = new Array();
					$.each(markers,function(i,marker){
						markers_ids.push(marker.id);
					});
					// ouvrir une infowindow multiple
					me.getInfowindow(markers_ids,e.layer.getLatLng());
				}
			}
			return false;
		});
		// lors de l'ajout d'un marker au cluster, vérifier si on doit ouvrir sa popup (uniquement au chargement)
		if (me.options.openInfowindow) {
			// brancher layeradd sur markerCluster._featureGroup, cf https://github.com/Leaflet/Leaflet.markercluster/issues/368
			me.markerCluster._featureGroup.on('layeradd', function (e) {
				if (e.layer.id==me.options.openInfowindow)
					me.getInfowindow(e.layer.id,e.layer.getLatLng());
				me.options.openInfowindow = false;
			});
		}
	}

});

L.map.geodiv = function (id, options) {
	return new L.Map.Geodiv(id, options);
};

})();