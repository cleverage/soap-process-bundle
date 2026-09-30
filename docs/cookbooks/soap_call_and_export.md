Call a SOAP service and export the response
===========================================

This recipe calls a SOAP method returning a list, filters and maps the response, then writes it to a CSV file.
It uses the public [CountryInfoService](http://webservices.oorsprong.org/websamples.countryinfo/CountryInfoService.wso)
through the `country_info` client declared in the [client reference](../reference/client.md#examples).

```yaml
clever_age_process:
    configurations:
        app.soap_export_european_countries:
            description: 'Export the European countries from the CountryInfoService'
            tasks:
                list_countries:
                    service: '@CleverAge\SoapProcessBundle\Task\RequestTask'
                    error_strategy: stop
                    options:
                        client: country_info
                        method: FullCountryInfoAllCountries
                    outputs: [extract]

                extract:
                    service: '@CleverAge\ProcessBundle\Task\TransformerTask'
                    options:
                        transformers:
                            property_accessor: # Get the list of countries from the stdClass response
                                property_path: 'FullCountryInfoAllCountriesResult.tCountryInfo'
                            array_filter:
                                condition:
                                    match:
                                        sContinentCode: 'EU'
                            array_map:
                                transformers:
                                    cast: # Convert each stdClass to an array
                                        type: array
                                    mapping:
                                        mapping:
                                            iso_code:
                                                code: '[sISOCode]'
                                            name:
                                                code: '[sName]'
                                            capital:
                                                code: '[sCapitalCity]'
                                            phone_code:
                                                code: '[sPhoneCode]'
                                            currency:
                                                code: '[sCurrencyISOCode]'
                    outputs: [iterate]

                iterate:
                    service: '@CleverAge\ProcessBundle\Task\InputIteratorTask'
                    outputs: [write]

                write:
                    service: '@CleverAge\ProcessBundle\Task\File\Csv\CsvWriterTask'
                    options:
                        file_path: '%kernel.project_dir%/var/exports/european_countries_{date}.csv'
                        headers: [iso_code, name, capital, phone_code, currency]
```

How it works:
- The [RequestTask](../reference/tasks/request_task.md) has no input (it is the first task), so the
  `FullCountryInfoAllCountries` method is called without argument. The response is a `stdClass` built by `SoapClient`.
  With `error_strategy: stop`, a failed call (logged with the last request and response) stops the process.
- The [TransformerTask](https://github.com/cleverage/process-bundle/blob/main/docs/reference/tasks/transformer_task.md)
  reads the list of countries in the response with
  [property_accessor](https://github.com/cleverage/process-bundle/blob/main/docs/reference/transformers/property_accessor_transformer.md),
  keeps the European ones with
  [array_filter](https://github.com/cleverage/process-bundle/blob/main/docs/reference/transformers/array_filter_transformer.md),
  then converts each `stdClass` to an array
  ([cast](https://github.com/cleverage/process-bundle/blob/main/docs/reference/transformers/cast_transformer.md)) and
  renames its keys ([mapping](https://github.com/cleverage/process-bundle/blob/main/docs/reference/transformers/mapping_transformer.md))
  inside [array_map](https://github.com/cleverage/process-bundle/blob/main/docs/reference/transformers/array_map_transformer.md).
- The [InputIteratorTask](https://github.com/cleverage/process-bundle/blob/main/docs/reference/tasks/input_iterator_task.md)
  outputs the countries one by one to the
  [CsvWriterTask](https://github.com/cleverage/process-bundle/blob/main/docs/reference/tasks/csv_writer_task.md).

Note that the client sets the `SOAP_SINGLE_ELEMENT_ARRAYS` feature: without it, `tCountryInfo` would be a single
`stdClass` instead of a list if the service returned only one country. Also, a failed call stops the process without
marking it as failed (see [RequestTask notes](../reference/tasks/request_task.md#notes)): monitor the error logs of
the `cleverage_process_task` channel.
