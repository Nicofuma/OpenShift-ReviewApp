Create project
--------------

curl --cacert /etc/origin/master/ca.crt -H "Authorization: Bearer $TOKEN" -H "Content-Type: application/json" "https://masterdns.westeurope.cloudapp.azure.com:8443/oapi/v1/projects" -X POST --data "@project.json"

Delete project
--------------

curl --cacert /etc/origin/master/ca.crt -H "Authorization: Bearer $TOKEN" -H "Content-Type: application/json" "https://masterdns.westeurope.cloudapp.azure.com:8443/oapi/v1/projects/{name}" -X DELETE

Delete All ReviewApp projects
-----------------------------

curl --cacert /etc/origin/master/ca.crt -H "Authorization: Bearer $TOKEN" -H "Content-Type: application/json" "https://masterdns.westeurope.cloudapp.azure.com:8443/oapi/v1/projects?labelSelector=usage%3Dreview-app" -X DELETE

Deploying the app
-----------------

Adds the self-provisioner to the service account used to run the pods
```
$ oc policy add-role-to-user self-provisioner system:self-provisioner:review-app:default
```

N.B Better use a dedicated service account for this pod
