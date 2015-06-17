from vegadns_client.common import AbstractResource, AbstractResourceCollection
from vegadns_client.exceptions import ClientException


class Domains(AbstractResourceCollection):
    def __call__(self, filter=None):
        # filter will be supported later
        r = self.client.get("/domains")
        if r.status_code != 200:
            raise ClientException(r.status_code, r.content)

        decoded = r.json()
        domains = []
        for domain in decoded["domains"]:
            d = Domain(self.client)
            d.values = domain
            domains.append(d)

        return domains

    def create(self, domain):
        r = self.client.post(
            "/domains",
            data={'domain': domain}
        )
        if r.status_code != 201:
            raise ClientException(r.status_code, r.content)
        decoded = r.json()
        m = Domain(self.client)
        m.values = decoded["domain"]

        return m


class Domain(AbstractResource):
    def __call__(self, domain_id):
        r = self.client.get("/domains/" + str(domain_id))
        if r.status_code != 200:
            raise ClientException(r.status_code, r.content)

        decoded = r.json()
        self.values = decoded["domain"]

        return self

    def delete(self):
        if self.values.get('domain_id', False) is False:
            raise ClientException(400, "domain_id is not set")

        r = self.client.delete(
            "/domains/" + str(self.values["domain_id"])
        )
        if r.status_code != 200:
            raise ClientException(r.status_code, r.content)

    def edit(self, owner_id=None, status=None):
        if self.values.get('domain_id', False) is False:
            raise ClientException(400, "domain_id is not set")

        data = {}
        if owner_id is not None:
            data["owner_id"] = owner_id
        if status is not None:
            data["status"] = status

        r = self.client.put(
            "/domains/" + str(self.values["domain_id"]),
            data=data
        )
        if r.status_code != 200:
            raise ClientException(r.status_code, r.content)

        decoded = r.json()
        self.values = decoded["domain"]

        return self
