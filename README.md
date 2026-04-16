# VulnDex Beacon for Nextcloud

The VulnDex Beacon for Nextcloud automatically collects version and system information and transmits it to the VulnDex platform.

The aim is to provide a central overview of installed instances and to cross-reference them with known vulnerabilities (CVEs) at an early stage.

An active VulnDex subscription and [API key](https://vulndex.at/docs/10-vulndex-docs/136-beacon-fur-moodle) is required.

## Features

- Automatic detection of the Nextcloud version
- Collection of installed apps and versions
- Regular status reports to VulnDex
- Centralised assignment to teams and services
- Basis for continuous vulnerability monitoring

## How it works

The Beacon is installed as an extension in Nextcloud and sends data to the VulnDex API at defined intervals.

The data is processed in VulnDex and enriched with up-to-date vulnerability information. This provides a continuous overview of affected systems.
