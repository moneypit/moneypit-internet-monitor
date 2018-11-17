new Vue({
  el: '#moneypit-internet-monitor',
  data: {
    device: '',
    location: '',
    internet_status: '',
    raw_status: {},
    last_updated: ''
  },

  methods: {

  },

  created () {
    var vm = this;

    axios.get('./api/config')
    .then(function (response) {
      vm.device = response.data.device;
      vm.location = response.data.location;
    })

    axios.get('./api/internet')
    .then(function (response) {

      vm.internet_status = response.data.internet.status;
      vm.last_updated = response.data.timestamp;
      vm.raw_status = response.data;
    })

  }
})
