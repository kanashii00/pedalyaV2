/* ============================================================
   PEDALYA - Shared GeoLibre 3D Map module
   ------------------------------------------------------------
   Single source of truth for the live GeoLibre / OpenStreetMap
   view used by both Monitoring (GeoLibre 3D Map) and Theft
   Detection. Renders the saved geofence boundary + warning
   threshold band + every live bicycle pin coloured strictly by
   geofence position (green=inside, orange=near, red=outside),
   using real GPS coordinates and the same client-side geofence
   math that mirrors GeofenceService::checkPoint.

   No hardcoded or duplicate map data: the geofence config and
   bicycle positions are provided by the caller from the same
   source of truth (GeofenceService + Bicycle.currentLat/Lng).

   Usage:
     window.PedalyaGeoLibre.init({
       container: 'monitoringMap',
       geofence: { ... } | null,
       bicycles: [ { id, name, lat, lng, status, battery, locked, heartbeat } ],
       liveUrl: '/admin/monitoring/live',
       pollMs: 15000,
       alertUrl: null,              // optional theft-alerts live url
       readout: { radius: 'geofenceRadiusText', alertBadge: 'geofenceAlertBadge' },
       buttons: { center: 'centerMapBtn', refresh: 'refreshMapBtn', fullscreen: 'fullscreenMapBtn' },
       legendCounts: true,
       bikeCardSelector: '.bike-monitor-card',
       fleetCount: 'fleetCount',
       alertBicycles: null,         // optional object: bikeId -> alert meta
       onAlertsChange: null         // optional callback(alerts)
     });
   ============================================================ */
(function (global) {
    'use strict';

    var zoneColor = {
        safe: '#2ecc71',
        approaching: '#f39c12',
        warning: '#f39c12',
        breach: '#e74c3c',
        outside: '#e74c3c',
        unknown: '#95a5a6'
    };

    function el(id) {
        return id ? document.getElementById(id) : null;
    }

    // --- Geometry helpers (mirror GeofenceService) ---

    function circlePolygon(lng, lat, radiusMeters, segments) {
        segments = segments || 96;
        var coords = [];
        var earth = 6371000;
        var latRad = lat * Math.PI / 180;
        var lngScale = earth * Math.cos(latRad);
        var latScale = earth;
        for (var i = 0; i <= segments; i++) {
            var rad = (i / segments) * 2 * Math.PI;
            var dLng = (Math.sin(rad) * radiusMeters) / lngScale;
            var dLat = (Math.cos(rad) * radiusMeters) / latScale;
            coords.push([lng + dLng * (180 / Math.PI), lat + dLat * (180 / Math.PI)]);
        }
        coords.push(coords[0]);
        return { type: 'Feature', geometry: { type: 'Polygon', coordinates: [coords] } };
    }

    function metersToLatLngShape(start, x, y) {
        var latRad = start.lat * Math.PI / 180;
        return {
            lng: start.lng + (x / (111320 * Math.cos(latRad))),
            lat: start.lat + (y / 111320)
        };
    }

    function shapeVertices(gf) {
        var start = { lat: gf.centerLat, lng: gf.centerLng };
        var type = gf.shapeType || 'circle';
        var radius = gf.radius || 500;
        var width = gf.width || radius || 500;
        var height = gf.height || radius || 500;
        if (type === 'rectangle') {
            var a = width / 2, b = height / 2;
            var th = (gf.rotation || 0) * Math.PI / 180, cos = Math.cos(th), sin = Math.sin(th);
            var corners = [[a, b], [-a, b], [-a, -b], [a, -b]];
            return corners.map(function (c) {
                var x = c[0] * cos - c[1] * sin;
                var y = c[0] * sin + c[1] * cos;
                var p = metersToLatLngShape(start, x, y);
                return [p.lng, p.lat];
            });
        }
        if (type === 'oval_h' || type === 'oval_v') {
            var a2 = Math.max(1, width / 2), b2 = Math.max(1, height / 2);
            var el = [];
            for (var i = 0; i < 96; i++) {
                var rad = (i / 96) * 2 * Math.PI;
                var p = metersToLatLngShape(start, Math.cos(rad) * a2, Math.sin(rad) * b2);
                el.push([p.lng, p.lat]);
            }
            return el;
        }
        if (type === 'polygon' && gf.points && gf.points.length >= 3) {
            return gf.points.map(function (p) { return [p.lng, p.lat]; });
        }
        var coords = [];
        for (var i2 = 0; i2 < 96; i2++) {
            var r2 = (i2 / 96) * 2 * Math.PI;
            var p2 = metersToLatLngShape(start, Math.cos(r2) * radius, Math.sin(r2) * radius);
            coords.push([p2.lng, p2.lat]);
        }
        return coords;
    }

    function shapeFeature(gf) {
        var verts = shapeVertices(gf);
        if (verts.length) verts.push(verts[0]);
        return { type: 'Feature', geometry: { type: 'Polygon', coordinates: [verts] } };
    }

    function setSourceData(map, id, feature) {
        if (map.getSource(id)) map.getSource(id).setData(feature);
        else map.addSource(id, { type: 'geojson', data: feature });
    }

    function addLayerOnce(map, layer) {
        if (!map.getLayer(layer.id)) map.addLayer(layer);
    }

    function safeRingVertices(gf) {
        var threshold = parseFloat(gf.warningThreshold) || 0;
        if (threshold <= 0) return [];
        var verts = shapeVertices(gf);
        var center = { lat: gf.centerLat, lng: gf.centerLng };
        var maxDim = Math.max(gf.radius || 0, gf.width || 0, gf.height || 0) * 0.5;
        threshold = Math.min(threshold, maxDim || threshold);
        var latRad = center.lat * Math.PI / 180;
        var dlng = threshold / (111320 * Math.cos(latRad));
        var dlat = threshold / 111320;
        return verts.map(function (v) {
            var dx = v[0] - center.lng;
            var dy = v[1] - center.lat;
            var m = Math.sqrt(dx * dx + dy * dy) || 1;
            return [v[0] - (dx / m) * dlng, v[1] - (dy / m) * dlat];
        });
    }

    function warnBandFeature(gf) {
        var outer = shapeVertices(gf).slice();
        if (outer.length) outer.push(outer[0]);
        var innerVerts = safeRingVertices(gf);
        if (!innerVerts.length) {
            return { type: 'Feature', geometry: { type: 'Polygon', coordinates: [outer] } };
        }
        var inner = innerVerts.slice().reverse();
        if (inner.length) inner.push(inner[0]);
        return { type: 'Feature', geometry: { type: 'Polygon', coordinates: [outer, inner] } };
    }

    function renderGeofence(map, gf, options) {
        setSourceData(map, 'geofence', shapeFeature(gf));
        addLayerOnce(map, {
            id: 'geofence-fill',
            type: 'fill',
            source: 'geofence',
            paint: { 'fill-color': '#27ae60', 'fill-opacity': 0.12 }
        });

        var hasThreshold = (parseFloat(gf.warningThreshold) || 0) > 0;
        setSourceData(map, 'warning-band', warnBandFeature(gf));
        addLayerOnce(map, {
            id: 'warning-band-fill',
            type: 'fill',
            source: 'warning-band',
            paint: { 'fill-color': '#ef5350', 'fill-opacity': 0.5 }
        });
        if (map.getLayer('warning-band-fill')) {
            map.setLayoutProperty('warning-band-fill', 'visibility', hasThreshold ? 'visible' : 'none');
        }

        addLayerOnce(map, {
            id: 'geofence-outline',
            type: 'line',
            source: 'geofence',
            paint: {
                'line-color': '#1e8449',
                'line-width': 3,
                'line-dasharray': [0, 2, 2, 2],
                'line-opacity': 0.9
            }
        });

        if (options.readout && options.readout.radius) {
            var radiusText = el(options.readout.radius);
            if (radiusText) radiusText.textContent = Math.round(gf.radius || 0) + 'm';
            var badge = el(options.readout.alertBadge);
            if (badge) {
                badge.innerHTML = gf.alertEnabled
                    ? '<span class="badge-admin badge-admin--success">Alerts ON</span>'
                    : '';
            }
        }
    }

    function zoneLabelText(zone) {
        switch (zone) {
            case 'safe': return 'Inside Zone';
            case 'approaching':
            case 'warning': return 'Near Boundary';
            case 'breach':
            case 'outside': return 'Outside Zone';
            default: return 'Unknown';
        }
    }

    function markerColor(bike) {
        switch (bike.zone) {
            case 'approaching':
            case 'warning':
                return zoneColor['warning'];
            case 'breach':
            case 'outside':
                return zoneColor['breach'];
            case 'safe':
            default:
                return bike.zone ? zoneColor['safe'] : zoneColor['unknown'];
        }
    }

    function haversineMeters(lat1, lng1, lat2, lng2) {
        var R = 6371000;
        var dLat = (lat2 - lat1) * Math.PI / 180;
        var dLng = (lng2 - lng1) * Math.PI / 180;
        var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLng / 2) * Math.sin(dLng / 2);
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }

    function localMeters(gf, lat, lng) {
        var centerLat = parseFloat(gf.centerLat) || 0;
        var centerLng = parseFloat(gf.centerLng) || 0;
        var latRad = centerLat * Math.PI / 180;
        return {
            x: (lng - centerLng) * (111320 * Math.cos(latRad)),
            y: (lat - centerLat) * 111320
        };
    }

    function latLng(v) {
        if (v && v.lat !== undefined && v.lng !== undefined) return v;
        return { lat: v[1], lng: v[0] };
    }

    function havDistToCenter(gf, lat, lng) {
        return haversineMeters(lat, lng, parseFloat(gf.centerLat), parseFloat(gf.centerLng));
    }

    function pointInside(gf, lat, lng) {
        var type = gf.shapeType || 'circle';
        if (type === 'polygon') {
            var pts = gf.points || [];
            if (pts.length < 3) return havDistToCenter(gf, lat, lng) <= (parseFloat(gf.radius) || 0);
            var inside = false;
            for (var i = 0, j = pts.length - 1; i < pts.length; j = i++) {
                var latI = parseFloat(pts[i].lat), lngI = parseFloat(pts[i].lng);
                var latJ = parseFloat(pts[j].lat), lngJ = parseFloat(pts[j].lng);
                var intersects = (lngI > lng) !== (lngJ > lng) &&
                    lat < (latJ - latI) * (lng - lngI) / (lngJ - lngI) + latI;
                if (intersects) inside = !inside;
            }
            return inside;
        }
        var m = localMeters(gf, lat, lng);
        if (type === 'rectangle') {
            var w = (parseFloat(gf.width) || parseFloat(gf.radius) || 500) / 2;
            var h = (parseFloat(gf.height) || parseFloat(gf.radius) || 500) / 2;
            var th = (parseFloat(gf.rotation) || 0) * Math.PI / 180;
            var cos = Math.cos(th), sin = Math.sin(th);
            var lx = m.x * cos - m.y * sin;
            var ly = m.x * sin + m.y * cos;
            return Math.abs(lx) <= w && Math.abs(ly) <= h;
        }
        if (type === 'oval_h' || type === 'oval_v') {
            var a = Math.max(1, (parseFloat(gf.width) || parseFloat(gf.radius) || 500) / 2);
            var b = Math.max(1, (parseFloat(gf.height) || parseFloat(gf.radius) || 500) / 2);
            return (m.x * m.x) / (a * a) + (m.y * m.y) / (b * b) <= 1;
        }
        return havDistToCenter(gf, lat, lng) <= (parseFloat(gf.radius) || 0);
    }

    function distanceToBoundary(gf, lat, lng) {
        var verts = shapeVertices(gf);
        var centerLat = parseFloat(gf.centerLat) || 0;
        var centerLng = parseFloat(gf.centerLng) || 0;
        if (verts.length < 2) {
            return Math.abs(haversineMeters(lat, lng, centerLat, centerLng) - (parseFloat(gf.radius) || 0));
        }
        var mPerDegLat = 111320;
        var mPerDegLng = 111320 * Math.cos(lat * Math.PI / 180);
        var min = Infinity;
        for (var i = 0; i < verts.length; i++) {
            var av = latLng(verts[i]);
            var bv = latLng(verts[(i + 1) % verts.length]);
            var ax = (av.lng - lng) * mPerDegLng;
            var ay = (av.lat - lat) * mPerDegLat;
            var bx = (bv.lng - lng) * mPerDegLng;
            var by = (bv.lat - lat) * mPerDegLat;
            var dx = bx - ax, dy = by - ay;
            var seg;
            if (dx === 0 && dy === 0) {
                seg = Math.sqrt(ax * ax + ay * ay);
            } else {
                var t = ((-ax) * dx + (-ay) * dy) / (dx * dx + dy * dy);
                t = Math.max(0, Math.min(1, t));
                var cx = ax + t * dx, cy = ay + t * dy;
                seg = Math.sqrt(cx * cx + cy * cy);
            }
            if (seg < min) min = seg;
        }
        return min;
    }

    function clientZoneFor(gf, lat, lng) {
        var dist = havDistToCenter(gf, lat, lng);
        var inside = pointInside(gf, lat, lng);
        var threshold = parseFloat(gf.warningThreshold) || 0;
        if (!inside) return { zone: 'breach', distance: dist };
        var toBoundary = distanceToBoundary(gf, lat, lng);
        if (threshold > 0) {
            if (toBoundary <= threshold * 0.5) return { zone: 'warning', distance: dist };
            if (toBoundary <= threshold) return { zone: 'approaching', distance: dist };
        }
        return { zone: 'safe', distance: dist };
    }

    function normalizeZone(bike, ctx) {
        if (bike.lat && bike.lng && ctx.geofence && ctx.geofence.centerLat !== undefined
                && ctx.geofence.centerLat !== null && ctx.geofence.centerLng !== undefined) {
            var res = clientZoneFor(ctx.geofence, bike.lat, bike.lng);
            return { zone: res.zone, distance: res.distance };
        }
        return { zone: bike.zone || 'unknown', distance: bike.distance };
    }

    // --- Rendering / live sync ---

    function buildContext(bike) {
        return { id: bike.id, name: bike.name || ('Bike #' + bike.id), bike: bike };
    }

    function init(options) {
        if (!global.maplibregl) return null;

        var container = el(options.container);
        if (!container) return null;

        var ctx = {
            options: options,
            geofence: options.geofence || null,
            map: null,
            markers: {},
            cycling: null
        };

        var gf = ctx.geofence;
        var map = new global.maplibregl.Map({
            container: container,
            style: 'https://tiles.openfreemap.org/styles/liberty',
            center: gf && gf.centerLng !== undefined && gf.centerLat !== undefined
                ? [gf.centerLng, gf.centerLat]
                : options.defaultCenter || [125.6470, 7.0990],
            zoom: options.zoom || 15,
            pitch: options.pitch || 55,
            bearing: options.bearing || -15,
            attributionControl: true
        });

        var nav = document.createElement('div');
        map.addControl(new global.maplibregl.NavigationControl(), 'top-right');
        map.addControl(new global.maplibregl.FullscreenControl(), 'top-right');
        ctx.map = map;

        function addMarker(bike) {
            if (!bike.lat || !bike.lng) return;
            var color = markerColor(bike);
            var dist = bike.distance !== null && bike.distance !== undefined
                ? '<br><small>Distance: ' + Math.round(bike.distance) + ' m</small>' : '';
            var zoneLabel = bike.zone ? zoneLabelText(bike.zone) : 'Unknown';
            var alert = options.alertBicycles && options.alertBicycles[bike.id];
            var alertHtml = alert
                ? '<br><small style="color:#e74c3c;font-weight:700;">&#9888; Theft alert' + (alert.status ? ' (' + alert.status + ')' : '') + '</small>'
                : '';
            var marker = new global.maplibregl.Marker({ color: color, pitchAlignment: 'auto', rotationAlignment: 'auto' })
                .setLngLat([bike.lng, bike.lat])
                .setPopup(new global.maplibregl.Popup({ offset: 30 }).setHTML(
                    '<strong>' + bike.name + '</strong><br>' +
                    '<small>Status: ' + bike.status + '</small><br>' +
                    '<small>Zone: ' + zoneLabel + '</small><br>' +
                    '<small>Battery: ' + bike.battery + '%</small><br>' +
                    '<small>Lock: ' + (bike.locked ? 'Locked' : 'Unlocked') + '</small>' + dist +
                    alertHtml +
                    '<br><small>Last heartbeat: ' + (bike.heartbeat ? new Date(bike.heartbeat).toLocaleString() : 'Never') + '</small>'
                ))
                .addTo(map);

            ctx.markers[bike.id] = { marker: marker, bike: bike };
        }

        function syncFleetStatus(bike) {
            if (!options.bikeCardSelector) return;
            var card = document.querySelector(options.bikeCardSelector + '[data-bike-id="' + bike.id + '"]');
            if (!card) return;
            var pill = card.querySelector('.zone-pill');
            if (pill) {
                var meta = zonePillMeta(bike.zone);
                pill.style.background = meta.bg + '22';
                pill.style.color = meta.bg;
                pill.innerHTML = '<span class="dot" style="background:' + meta.bg + ';"></span>' + meta.label;
            }
            var gpsCells = card.querySelectorAll('small.fw-semibold');
            if (bike.lat && bike.lng && gpsCells.length > 1) {
                var gps = null;
                gpsCells.forEach(function (s) {
                    if (!gps && /^-?\d{1,3}\.\d+/.test(s.textContent || '')) gps = s;
                });
                if (gps) gps.textContent = Number(bike.lat).toFixed(4) + ', ' + Number(bike.lng).toFixed(4);
            }
        }

        function updateLegendCounts() {
            if (!options.legendCounts) return;
            var counts = { safe: 0, near: 0, outside: 0 };
            Object.keys(ctx.markers).forEach(function (id) {
                var zone = ctx.markers[id].bike.zone;
                if (zone === 'breach' || zone === 'outside') counts.outside++;
                else if (zone === 'approaching' || zone === 'warning') counts.near++;
                else if (zone === 'safe') counts.safe++;
            });
            var keys = { safe: 'safe', near: 'near', outside: 'outside' };
            Object.keys(keys).forEach(function (key) {
                var node = document.querySelector('.map-legend .legend-count[data-count="' + keys[key] + '"]');
                if (node) node.textContent = String(counts[key]);
            });
        }

        function updateMarker(bike) {
            if (!bike.lat || !bike.lng) {
                if (ctx.markers[bike.id]) {
                    ctx.markers[bike.id].marker.remove();
                    delete ctx.markers[bike.id];
                }
                if (bike.zone) {
                    bike.zone = 'no-gps';
                    bike.distance = null;
                    syncFleetStatus(bike);
                    updateLegendCounts();
                }
                return;
            }
            var norm = normalizeZone(bike, ctx);
            bike.zone = norm.zone;
            if (bike.distance === null || bike.distance === undefined) bike.distance = norm.distance;

            var isBreach = bike.zone === 'breach' || bike.zone === 'outside';
            var color = markerColor(bike);
            if (ctx.markers[bike.id]) {
                ctx.markers[bike.id].marker.setLngLat([bike.lng, bike.lat]);
                ctx.markers[bike.id].marker.setColor(color);
                var markerEl = ctx.markers[bike.id].marker.getElement();
                if (markerEl) {
                    if (isBreach) markerEl.classList.add('marker-breach');
                    else markerEl.classList.remove('marker-breach');
                }
                ctx.markers[bike.id].bike = bike;
            } else {
                addMarker(bike);
                if (isBreach) {
                    var markerEl2 = ctx.markers[bike.id].marker.getElement();
                    if (markerEl2) markerEl2.classList.add('marker-breach');
                }
            }
            syncFleetStatus(bike);
            updateLegendCounts();
        }

        function applyLive(data) {
            if (data.geofence && JSON.stringify(data.geofence) !== JSON.stringify(ctx.geofence)) {
                ctx.geofence = data.geofence;
                renderGeofence(map, ctx.geofence, options);
            }
            (data.bicycles || []).forEach(function (bike) {
                updateMarker({
                    id: bike.id,
                    name: bike.name,
                    lat: bike.current_lat !== undefined ? parseFloat(bike.current_lat) : bike.currentLat,
                    lng: bike.current_lng !== undefined ? parseFloat(bike.current_lng) : bike.currentLng,
                    status: bike.status,
                    battery: bike.battery_level !== undefined ? bike.battery_level : bike.batteryLevel,
                    locked: bike.lock_status === 'locked' || bike.lockStatus === 'locked',
                    heartbeat: bike.last_heartbeat || bike.lastHeartbeat,
                    zone: bike.zone_level || (bike.zone ? bike.zone.level : null) || 'unknown',
                    distance: bike.zone_distance || (bike.zone ? bike.zone.distance : null) || null
                });
            });
            if (options.alertBicycles && data.alerts) {
                options.alertBicycles = {};
                data.alerts.forEach(function (a) {
                    options.alertBicycles[a.bicycleId] = { status: a.status, acknowledged: a.acknowledged };
                });
            }
            if (options.onAlertsChange && data.alerts) {
                options.onAlertsChange(data);
            }
        }

        function fetchLive() {
            var url = options.liveUrl;
            if (!url) return;
            global.fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(applyLive)
                .catch(function () {});
        }

        function startPolling() {
            if (ctx.cycling) return;
            ctx.cycling = setInterval(fetchLive, options.pollMs || 15000);
        }

        map.on('load', function () {
            if (ctx.geofence) renderGeofence(map, ctx.geofence, options);
            (options.bicycles || []).forEach(function (bike) {
                updateMarker(initBikeShape(bike));
            });

            var centerBtn = el(options.buttons && options.buttons.center);
            if (centerBtn && ctx.geofence) {
                centerBtn.addEventListener('click', function () {
                    map.flyTo({
                        center: [ctx.geofence.centerLng, ctx.geofence.centerLat],
                        zoom: options.zoom || 15,
                        pitch: options.pitch || 55,
                        bearing: options.bearing || -15,
                        duration: 1000
                    });
                });
            }
            var refreshBtn = el(options.buttons && options.buttons.refresh);
            if (refreshBtn) refreshBtn.addEventListener('click', fetchLive);
            var fsBtn = el(options.buttons && options.buttons.fullscreen);
            if (fsBtn) {
                fsBtn.addEventListener('click', function () {
                    var c = container;
                    if (c.requestFullscreen) c.requestFullscreen();
                    else if (c.webkitRequestFullscreen) c.webkitRequestFullscreen();
                    else if (c.msRequestFullscreen) c.msRequestFullscreen();
                });
            }

            // WebSocket live updates (fallback to polling)
            var broadcastOn = global.Pedalya && global.Pedalya.broadcastEnabled && global.Echo;
            if (broadcastOn) {
                global.Echo.private('geofence-alerts').listen('GeofenceAlert', function (e) {
                    var bike = e && e.bicycle ? e.bicycle : null;
                    if (!bike) return;
                    var zone = bike.zone || e.level || 'unknown';
                    updateMarker({
                        id: bike.id,
                        name: bike.name,
                        lat: bike.lat !== undefined ? parseFloat(bike.lat) : bike.latitude,
                        lng: bike.lng !== undefined ? parseFloat(bike.lng) : bike.longitude,
                        status: bike.status,
                        battery: bike.battery !== undefined ? bike.battery : bike.battery_level,
                        locked: bike.locked === true || bike.lockStatus === 'locked',
                        heartbeat: bike.updated_at || bike.last_heartbeat,
                        zone: zone,
                        distance: bike.zone_distance !== undefined ? parseFloat(bike.zone_distance) : (bike.distance !== undefined ? parseFloat(bike.distance) : null)
                    });
                    if (options.onBreachEvent) options.onBreachEvent(bike, zone);
                });

                global.Echo.private('geofence-alerts').listen('GeofenceUpdated', function (e) {
                    if (!e || !e.geofence) return;
                    ctx.geofence = e.geofence;
                    renderGeofence(map, ctx.geofence, options);
                    Object.keys(ctx.markers).forEach(function (id) {
                        var mk = ctx.markers[id];
                        if (mk && mk.bike && mk.bike.lat && mk.bike.lng) updateMarker(mk.bike);
                    });
                });

                (options.bicycles || []).forEach(function (bike) {
                    global.Echo.private('gps.' + bike.id).listen('GpsUpdate', function (e) {
                        var b = e && e.bicycle ? e.bicycle : null;
                        if (!b) return;
                        updateMarker({
                            id: b.id,
                            name: b.name,
                            lat: b.lat !== undefined ? parseFloat(b.lat) : b.latitude,
                            lng: b.lng !== undefined ? parseFloat(b.lng) : b.longitude,
                            status: b.status,
                            battery: b.battery !== undefined ? b.battery : b.battery_level,
                            locked: b.locked === true || b.lockStatus === 'locked',
                            heartbeat: b.updated_at || b.last_heartbeat,
                            zone: b.zone || 'unknown',
                            distance: b.zone_distance !== undefined ? parseFloat(b.zone_distance) : null
                        });
                    });
                });
            } else {
                startPolling();
            }
        });

        return ctx;
    }

    function initBikeShape(bike) {
        return {
            id: bike.id,
            name: bike.name,
            lat: bike.lat !== undefined ? parseFloat(bike.lat) : bike.currentLat,
            lng: bike.lng !== undefined ? parseFloat(bike.lng) : bike.currentLng,
            status: bike.status,
            battery: bike.battery !== undefined ? bike.battery : bike.batteryLevel,
            locked: bike.locked === true || bike.lockStatus === 'locked',
            heartbeat: bike.heartbeat || bike.lastHeartbeat,
            zone: bike.zone,
            distance: bike.distance
        };
    }

    function zonePillMeta(zone) {
        switch (zone) {
            case 'breach':
            case 'outside': return { bg: '#e74c3c', label: 'Outside Zone' };
            case 'warning':
            case 'approaching': return { bg: '#f39c12', label: 'Near Boundary' };
            case 'safe': return { bg: '#2ecc71', label: 'Inside Zone' };
            default: return { bg: '#95a5a6', label: 'No GPS' };
        }
    }

    global.PedalyaGeoLibre = {
        init: init,
        clientZoneFor: clientZoneFor,
        pointInside: pointInside
    };
})(window);
