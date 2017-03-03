## Result Size

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

> Avg = average.

## Not Compressed

1. **SQL => 4.333.821 byte**

2. **CSV => 4.213.821 byte**

3.  **JSON => 6.766.485 byte (Pretty Print Disabled)**

3.1. **JSON => 12.159.586 byte (Pretty Print Enabled)**

## Compressed

1. **SQL => 557.729 byte** (_-87%_)

2. **CSV => 543.328 byte** (_-87%_)

3.  **JSON => 616.249 byte (Pretty Print Disabled)** (_-90%_)

3.1. **JSON => 678.326 byte (Pretty Print Enabled)** (_-94%_)