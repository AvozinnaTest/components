(function () {
    'use strict';

    [].forEach.call(document.querySelectorAll('.card-actions_visible'), function(div) {
        div.addEventListener('click', function(e) {
            var favPage = new FavoritePage(e.target);
            favPage.clickHandler(e);
        });
    });
    document.querySelector('.personal-favorites-select__remove').addEventListener('click', function(e) {
        var favPage = new FavoritePage(e.target);
        favPage.delAllCheckedFav();
    });
    document.querySelector('.personal-favorites-select__button').addEventListener('click', function(e) {
        var favPage = new FavoritePage(e.target);
        favPage.AddToCartCheckedFav();
    });
    document.querySelector('.personal-favorites-search__button').addEventListener('click', function(e) {
        var favPage = new FavoritePage(e.target);
        favPage.SearchFav();
    });


    var FavoritePage = /*#__PURE__*/function () {
        function FavoritePage(targ) {
            this.shelf = targ;
            this.FavId = targ.getAttribute('data-fav');
            this.selectCountElement = document.querySelector('.personal-favorites-select__count');
            this.checkedFavs = document.querySelectorAll('.card-action_check_active');
            this.URL_FAV = '/local/components/dial/dialfavorite/ajax.php';
            this.searchInput = document.querySelector('.personal-favorites-search__input');
        }

        var _proto = FavoritePage.prototype;

        _proto.clickHandler = function clickHandler(e) {
            if (e.target && e.target.classList.contains("card-action_check")) {
                this.checkFav(e.target);
            }

            if (e.target && e.target.classList.contains("card-action_remove__svg")) {
                this.delFav();
            }

        };

        _proto.checkFav = function checkFav(el) {
            this.selectCountElement.innerHTML = this.checkedFavs.length;
        };

        _proto.delFav = function delFav() {
            BX.ajax({
                url: this.URL_FAV,
                timeout: 30,
                method: 'POST',
                data: {
                    ACTION: 'DELETE',
                    ID: this.FavId
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

        _proto.delAllCheckedFav = function delAllCheckedFav() {
            var url = this.URL_FAV;
            [].forEach.call(this.checkedFavs, function (div) {
                var id = div.getAttribute('data-fav');
                BX.ajax({
                    url: url,
                    timeout: 10,
                    method: 'POST',
                    data: {
                        ACTION: 'DELETE',
                        ID: id
                    },
                    dataType: 'json',
                    processData: false,
                    start: true,
                    onsuccess: BX.delegate(function (response) {
                        var data = JSON.parse(response);
                    }, this)
                });
            });
            location.reload();
        };

        _proto.AddToCartCheckedFav = function AddToCartCheckedFav() {
            var url = "/local/ajax/add2basket.php";
            [].forEach.call(this.checkedFavs, function (div) {
                var id = div.getAttribute('data-offer');
                BX.ajax({
                    url: url,
                    timeout: 10,
                    method: 'POST',
                    data: {
                        id: id
                    },
                    dataType: 'json',
                    processData: false,
                    start: true,
                    onsuccess: BX.delegate(function (response) {
                        document.querySelector('.header-cart').innerHTML = response;
                    }, this)
                });
            });
        };

        _proto.SearchFav = function SearchFav() {
            if (this.searchInput.value.length > 2) {
                var text = this.searchInput.value;
                var url = this.URL_FAV;
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
                        document.querySelector('.personal-favorites__list').outerHTML = response;
                    }, this)
                });
            }
        };

        return FavoritePage;
    }();


})();

