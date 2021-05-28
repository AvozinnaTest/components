(function () {
    'use strict';

  [].forEach.call(document.querySelectorAll('.popup-city__link'), function(linked) {
        linked.addEventListener('click', function(e) {
            var regions = new DialRegions(e.target);
            regions.setCity();
        });
    });

    document.querySelector('.popup-search__button').addEventListener('click', function(e) {
        e.preventDefault();
        var regions = new DialRegions(e.target);
        regions.SearchCity();
    });

    document.querySelector('.popup-regions-search').addEventListener('keyup', function(e) {
        e.preventDefault();
        var regions = new DialRegions(e.target);
        regions.SearchCity();
    });

    document.querySelector('.js-cityagree').addEventListener('click', function(e) {
        e.preventDefault();
        var regions = new DialRegions(e.target);
        regions.CityAgree();
    });


    "use strict";

    var DialRegions = /*#__PURE__*/function () {
        function DialRegions(targ) {
            this.shelf = targ;
            this.CityId = targ.getAttribute('data-id');
            this.URL_REGION = '/local/components/dial/regions/ajax.php';
            this.searchInput = document.querySelector('.popup-regions-search');
        }

        var _proto = DialRegions.prototype;

        _proto.clickHandler = function clickHandler(e) {};

        _proto.setCity = function setCity() {
            BX.ajax({
                url: this.URL_REGION,
                timeout: 10,
                method: 'POST',
                data: {
                    ACTION: 'CHANGE',
                    ID: this.CityId
                },
                dataType: 'json',
                processData: false,
                start: true,
                onsuccess: BX.delegate(function (response) {
                    var data = JSON.parse(response);
                    location.reload();
                }, this)
            });
        };

        _proto.SearchCity = function SearchCity() {
            if (this.searchInput.value.length > 2) {
                var text = this.ucFirst(this.searchInput.value);
                var url = this.URL_REGION;
                BX.ajax({
                    url: url,
                    timeout: 10,
                    method: 'POST',
                    data: {
                        ACTION: 'SEARCH',
                        TEXT: text
                    },
                    dataType: 'json',
                    processData: false,
                    start: true,
                    onsuccess: BX.delegate(function (response) {
                        response = JSON.parse(response);
                        console.log(response);
                        document.querySelector('.popup-city__items').innerHTML = response.searchResultStr;
                        var script = document.createElement('script');
                        script.setAttribute('type', 'text/javascript');
                        script.setAttribute('src', response.JSCLASS);
                        document.querySelector('.popup-city__navigation').appendChild(script);
                    }, this)
                });
            }
        };

        _proto.CityAgree = function CityAgree() {
            console.log('here');
            BX.ajax({
                url: this.URL_REGION,
                timeout: 10,
                method: 'POST',
                data: {
                    ACTION: 'ISSURE'
                },
                dataType: 'json',
                processData: false,
                start: true,
                onsuccess: BX.delegate(function (response) {
                    var el = document.querySelector('.header-city-ask');
                    var className = '_showing';
                    if (el.classList) el.classList.remove(className);else el.className = el.className.replace(new RegExp('\\b' + className + '\\b', 'g'), '');
                }, this)
            });
        };

        _proto.ucFirst = function ucFirst(str) {
            if (!str) return str;
            return str[0].toUpperCase() + str.slice(1);
        };

        return DialRegions;
    }();

})();

