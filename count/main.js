$(function() {
    //實例-初始化vue
    var vm = new Vue({
        //對應的視圖區域
        el: '#vueApp',
        //資料 model
        data: {
            theCounterData: [{
                count: 0,
                name: ""
            }]
        },
        mounted: function() {
            var theCounterObj = localStorage.getItem('aidecTinyProjectTheCounter');
            var theCounterJson = JSON.parse(theCounterObj);

            if (theCounterJson != null) {
                this.theCounterData = theCounterJson;
            }
            //console.log('theCounterObj: ', JSON.parse(theCounterObj));
        },
        //函數-方法 
        methods: {
            setCounter: function(item) {
                var self = this;
                swal({
                    title: '設定數值',
                    input: 'text',
                    showCancelButton: true,
                    confirmButtonText: '確認',
                    cancelButtonText: '取消',
                    showLoaderOnConfirm: true,
                    allowOutsideClick: false
                }).then(function(sVal) {
                    if (!isNaN(sVal)) {
                        item.count = parseInt(sVal);
                        self.storageData();
                    } else {
                        swal({
                            type: 'warning',
                            title: '提示',
                            html: '必須輸入數值'
                        })
                    }

                });
            },
            setCounterName: function(item) {
                var self = this;
                swal({
                    title: '設定計數器名稱',
                    input: 'text',
                    showCancelButton: true,
                    confirmButtonText: '確認',
                    cancelButtonText: '取消',
                    showLoaderOnConfirm: true,
                    allowOutsideClick: false
                }).then(function(sVal) {
                    item.name = sVal;
                    self.storageData();

                });
            },
            plusCounter: function(item) {
                item.count += 1;
                this.storageData();
            },
            clearCounter: function(item) {
                var self = this;
                swal({
                    title: '提示',
                    text: '確定要歸零此組計數器',
                    type: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#999',
                    confirmButtonText: '確認歸零',
                    cancelButtonText: '取消'
                }).then(function() {
                    //row 指此列物件
                    item.count = 0;
                    self.storageData();
                });
            },
            addCounter: function() {
                this.theCounterData.push({
                    count: 0
                });
                this.storageData();
            },
            delCounter: function(index) {
                var self = this;
                swal({
                    title: '刪除',
                    text: '確定要刪除此組計數器',
                    type: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#999',
                    confirmButtonText: '確認刪除',
                    cancelButtonText: '取消'
                }).then(function() {
                    //row 指此列物件
                    self.theCounterData.splice(index, 1);
                    self.storageData();
                });
            },
            //保存資料
            storageData: function() {
                localStorage.setItem('aidecTinyProjectTheCounter', JSON.stringify(this.theCounterData));
            },
        }
    });

});