from flask import Flask, abort, redirect, url_for
from flask.ext.restful import Resource, Api, abort

from vegadns.api import endpoint
from vegadns.api.endpoints import AbstractEndpoint
from vegadns.api.models.domain import Domain as ModelDomain


@endpoint
class Domains(AbstractEndpoint):
    route = '/domains'

    def get(self):
        try:
            domains = []
            for domain in self.get_domain_list():
                domains.append(domain.to_dict())
        except:
            abort(404, message="no domains found")
        return {'status': 'ok', 'domains': domains}

    def get_domain_list(self):
        return ModelDomain.select()  # FIXME need authorization