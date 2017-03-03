## RESULT EXECUTION TIME

> All tests were run on the same machine and the same table.

```json
{
   "alldata": {
        "R": 35954,
        "C": 14
    }
}
```
- Rows: 35954
- Columns: 14

### Only Output (No Save)(x25)

1. SQL "Avg": 0.9852592182159424 <<<--- Time (float) [0.98s]

2. CSV "Avg": 0.5024807643890381

3. JSON "Avg": 0.14181912422180176

### Output in file (x10)

```json
{
    "SQL_NOCOMPRESSED_X10": 1.0164793252944946,
    "CSV_NOCOMPRESSED_X10": 0.9419022798538208,
    "JSON_NOCOMPRESSED_X10": 0.7656285047531128,
    "SQL_COMPRESSED_X10(No-separate)": 1.8855135440826416,
    "CSV_COMPRESSED_X10(No-separate)": 1.4471263885498047,
    "JSON_COMPRESSED_X10(No-separate)": 0.8811930894851685,
    "SQL_COMPRESSED_X10(Separate)": 1.8455211400985718,
    "CSV_COMPRESSED_X10(Separate)": 1.4210652589797974,
    "JSON_COMPRESSED_X10(Separate)": 0.8462664842605591
}
```

> Note: PRETTY PRINT active not influence.


