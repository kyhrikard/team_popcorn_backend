const csv = require('csvtojson')
const express = require('express')
const request = require('request')
const app = express()


const masterUrl = 'https://docs.google.com/spreadsheets/d/e/2PACX-1vRjf7u96UJhWMAZpNYncsozqxx38kCSLERWb7nLQZZ3zuK5leKFYnpnFjPQAlQWU6nDls6kkT9RDT44/pub?output=csv'
let masterArray = []
let studentsArray = []

function readDataFromCSV() {
    masterArray = []
    csv()
        .fromStream(request.get(masterUrl))
        .on('json', (jsonClassObj) => {
            csv()
                .fromStream(request.get(jsonClassObj.csvLink))
                .on('json', (jsonObj) => {
                    studentsArray.push(jsonObj)
                })
                .on('done', () => {
                    masterArray.push({
                        className: jsonClassObj.className,
                        students: studentsArray
                    })
                    studentsArray = []
                })
        })
}

app.get('/updatedata', function (req, res) {
    readDataFromCSV(masterUrl)
    res.send('updating data')
})

app.get('/alldata', function (req, res) {
    res.send(masterArray)
})

app.listen(3000)