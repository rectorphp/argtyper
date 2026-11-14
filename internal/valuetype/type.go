package valuetype

import "strings"

// Type is a value type: a scalar keyword, "callable", or an object of a class.
// The zero value Unknown means the type could not be resolved.
type Type string

const (
	Unknown  Type = ""
	Int      Type = "int"
	Float    Type = "float"
	String   Type = "string"
	Bool     Type = "bool"
	Array    Type = "array"
	Null     Type = "null"
	Callable Type = "callable"
)

const objectPrefix = "object:"

// Object returns the object type of a class, short or fully qualified.
func Object(class string) Type {
	return Type(objectPrefix + class)
}

// IsObject reports whether the type is an object type.
func (t Type) IsObject() bool {
	return strings.HasPrefix(string(t), objectPrefix)
}

// Class returns the class of an object type, or "" for any other type.
func (t Type) Class() string {
	class, ok := strings.CutPrefix(string(t), objectPrefix)
	if !ok {
		return ""
	}
	return class
}
