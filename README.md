# moneypit-internet-monitor

Used to monitor internet status of crypto mine, to ensure the money pit still has access to the internet.

Designed to run on a Raspberry Pi 3b+ (w/ RASPBIAN LITE v2.9) from within the local facility.

Internet status is also indexed in offsite Elasticsearch instance when internet is available.  

If connectivity to the internet (and therefore ES instance) is not available, results will be queued for indexing once connectivity is restored.

> Feedback on internet status is used in other monitoring jobs to take actions as appropriate.

## Dependencies
>
> Recommend  `sudo apt-get update` if fresh install

- Git
   `sudo apt-get install git -y`

- Python 2 w/ pip
  `sudo apt-get install python-pip -y`
  `sudo python -m pip install --upgrade pip setuptools wheel`

- Redis Server
   `sudo apt-get install redis-server -y`

- Npm / Node
   `sudo apt-get install npm -y`
   `sudo apt-get install nodejs -y`

- Python library for Elasticsearch and Redis
  `sudo pip install elasticsearch`
  `sudo pip install redis`

- PHP CLI / Curl
  `sudo apt-get install php7.0-cli -y`
  `sudo apt-get install php7.0-curl -y`

- A remote or local `elasticsearch` instance to post stats to (example: https://www.elastic.co/cloud)

## Install

- Clone repo `git clone https://github.com/moneypit/moneypit-internet-monitor`

- Rename `config_sample.json` to `config.json`

- Update config to change elasticsearch host and elasticsearch index name (if necessary).


- Enable `redis-server` service is start on reboot

`sudo systemctl enable redis-server`


- Configure node / redis to start following reboot `/etc/rc.local`

```

	#!/bin/sh -e
	#
	# rc.local
	#
	# This script is executed at the end of each multiuser runlevel.
	# Make sure that the script will "exit 0" on success or any other
	# value on error.
	#
	# In order to enable or disable this script just change the execution
	# bits.
	#
	# By default this script does nothing.

	# Print the IP address
	_IP=$(hostname -I) || true
	if [ "$_IP" ]; then
	  printf "My IP address is %s\n" "$_IP"
	fi

	# Start moneypit-power-monitor node app / api
	sudo /usr/bin/npm start --cwd /home/pi/moneypit-power-monitor --prefix /home/pi/moneypit-power-monitor &

  # Start power monitoring script
  sudo /usr/bin/python /home/pi/moneypit-power-monitor/scripts/fetch-power.py /home/pi/moneypit-power-monitor/config.json &

	exit 0

```

- From within the `./moneypit-fan-controller-folder` install Node dependencies

  ```
   $ npm install
  ```

- Setup the following cron jobs:

```
* * * * * python /home/pi/moneypit-power-monitor/scripts/post-power.py /home/pi/moneypit-power-monitor/config.json
```

- Reboot the device to start processes

```
sudo reboot
```

## UI

`http://[hostname]:3000/`

## APIs

`GET /` => Swagger docs

`GET /power` => Returns current power information