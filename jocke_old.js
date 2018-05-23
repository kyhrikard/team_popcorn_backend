const csv = require('csvtojson')
const express = require('express')
const request = require('request')
const app = express()

const masterUrl = 'https://docs.google.com/spreadsheets/d/e/2PACX-1vRjf7u96UJhWMAZpNYncsozqxx38kCSLERWb7nLQZZ3zuK5leKFYnpnFjPQAlQWU6nDls6kkT9RDT44/pub?output=csv'

const readClassSheet = ( { className, csvLink } ) => new Promise( ( resolve, reject ) => {
    const students = [];
    csv()
        .fromStream(request.get(csvLink))
        .on('json', (data) => {
            students.push(data)
        })
        .on('done', () =>
            resolve( { className, students } ) )
        .on( 'error', reject )
} );

const readMasterSheet = ( url ) => new Promise( ( resolve, reject ) => {
    const arr = [];
    csv()
        .fromStream(request.get(url))
        .on('json', ( data ) => arr.push( data ) )
        .on( 'done', () => {
            Promise
                .all( arr.map( readClassSheet ) )
                .then( resolve )
        } )
        .on('error', reject );
} );

app.get('/all', ( req, res ) => {
    readMasterSheet(masterUrl)
        .then( data => res.json( data ) );
} );

const readMasterSheet2 = ( url, className ) => new Promise( ( resolve, reject ) => {
    const arr = [];
    csv()
        .fromStream(request.get(url))
        .on('json', ( data ) => arr.push( data ) )
        .on( 'done', () => {
            const row = arr.find( ( data ) => ( className === data.className ) );
            resolve( row );
        } )
        .on('error', reject );
} );

app.get('/class/:className', ( req, res ) => {
    readMasterSheet2(masterUrl, req.params.className )
        .then( item => {
            if( item )
                return readClassSheet( item );
            else
                return 'class not found';
        } )
        .then( data => res.json( data ) );

} );
    
app.get('/class/:className/:secret', ( req, res ) => {
    readMasterSheet2(masterUrl, req.params.className )
        .then( item => {
            if( item ) {
                return readClassSheet( item )
                    .then( ( { students } ) => {
                        const student = students.find( ( student ) => ( student.secret === req.params.secret ) )
                        if( student )
                            return student;
                        else
                            return 'student not found';
        
                    } );                
            }
            else
                return 'class not found';
        } )
        .then( data => res.json( data ) );

} );
app.listen(3000)


    