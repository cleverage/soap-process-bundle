Enrich a CSV file with a SOAP service
=====================================

This recipe reads a CSV file of country codes, calls a SOAP method for each line, and writes the enriched lines to
another CSV file. Lines for which the call fails are logged and skipped. It uses the public
[CountryInfoService](http://webservices.oorsprong.org/websamples.countryinfo/CountryInfoService.wso) through the
`country_info` client declared in the [client reference](../reference/client.md#examples).

Source file `var/data/countries.csv`:

```csv
iso;label
FR;Our label for France
DE;Our label for Germany
```

```yaml
clever_age_process:
    configurations:
        app.soap_enrich_countries:
            description: 'Add the capital city and currency of each country'
            tasks:
                read:
                    service: '@CleverAge\ProcessBundle\Task\File\Csv\CsvReaderTask'
                    options:
                        file_path: '%kernel.project_dir%/var/data/countries.csv'
                    outputs: [build_arguments]

                build_arguments:
                    service: '@CleverAge\ProcessBundle\Task\TransformerTask'
                    options:
                        transformers:
                            mapping: # { parameters: { sCountryISOCode: FR } }
                                mapping:
                                    parameters:
                                        code: '[iso]'
                                        transformers:
                                            wrapper:
                                                wrapper_key: sCountryISOCode
                    outputs: [get_country]

                get_country:
                    service: '@CleverAge\SoapProcessBundle\Task\RequestTask'
                    error_strategy: skip
                    options:
                        client: country_info
                        method: FullCountryInfo
                    outputs: [map_response]
                    error_outputs: [log_error]

                log_error:
                    service: '@CleverAge\ProcessBundle\Task\Reporting\LoggerTask'
                    options:
                        level: warning
                        message: 'FullCountryInfo call failed, line skipped'

                map_response:
                    service: '@CleverAge\ProcessBundle\Task\TransformerTask'
                    options:
                        transformers:
                            property_accessor:
                                property_path: FullCountryInfoResult
                            cast:
                                type: array
                            mapping:
                                mapping:
                                    iso:
                                        code: '[sISOCode]'
                                    name:
                                        code: '[sName]'
                                    capital:
                                        code: '[sCapitalCity]'
                                    currency:
                                        code: '[sCurrencyISOCode]'
                    outputs: [write]

                write:
                    service: '@CleverAge\ProcessBundle\Task\File\Csv\CsvWriterTask'
                    options:
                        file_path: '%kernel.project_dir%/var/exports/countries_enriched_{date}.csv'
                        headers: [iso, name, capital, currency]
```

How it works:
- [CsvReaderTask](https://github.com/cleverage/process-bundle/blob/main/docs/reference/tasks/csv_reader_task.md) is
  iterable: each line goes through the following tasks before the next one is read, so one SOAP call is made per line.
- The first [TransformerTask](https://github.com/cleverage/process-bundle/blob/main/docs/reference/tasks/transformer_task.md)
  builds the arguments of the SOAP method with the
  [mapping](https://github.com/cleverage/process-bundle/blob/main/docs/reference/transformers/mapping_transformer.md)
  and [wrapper](https://github.com/cleverage/process-bundle/blob/main/docs/reference/transformers/wrapper_transformer.md)
  transformers: `FullCountryInfo` is a document/literal method expecting a single `parameters` structure.
- The [RequestTask](../reference/tasks/request_task.md) calls `FullCountryInfo` with these arguments. With
  `error_strategy: skip`, a failed call is logged by the task, the SOAP arguments built from the line are sent to the
  [LoggerTask](https://github.com/cleverage/process-bundle/blob/main/docs/reference/tasks/logger_task.md) of the
  `error_outputs`, and the line is not written.
- The second TransformerTask extracts the result from the `stdClass` response
  ([property_accessor](https://github.com/cleverage/process-bundle/blob/main/docs/reference/transformers/property_accessor_transformer.md)),
  converts it to an array ([cast](https://github.com/cleverage/process-bundle/blob/main/docs/reference/transformers/cast_transformer.md))
  and maps the columns to write with the
  [CsvWriterTask](https://github.com/cleverage/process-bundle/blob/main/docs/reference/tasks/csv_writer_task.md).

Note that the error output of the RequestTask is its input (the SOAP arguments), not the CSV line: the details of the
failed call (options, last request and response) are in the log context of the `Empty resultset for query` error
logged by the task.

To add a single value to the line instead of replacing it, the SOAP call can also be done inside a `mapping` with the
[RequestTransformer](../reference/transformers/request_transformer.md) (`soap_request`), e.g. with `keep_input: true`:

```yaml
# Task configuration level
add_country_name:
  service: '@CleverAge\ProcessBundle\Task\TransformerTask'
  options:
    transformers:
      mapping:
        keep_input: true
        mapping:
          name:
            code: '[iso]'
            transformers:
              wrapper:
                wrapper_key: sCountryISOCode
              wrapper#2:
                wrapper_key: parameters
              soap_request:
                client: country_info
                method: CountryName
              property_accessor:
                property_path: CountryNameResult
  outputs: [write]
```
